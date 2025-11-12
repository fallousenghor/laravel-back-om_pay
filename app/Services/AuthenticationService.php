<?php

namespace App\Services;

use App\Models\OrangeMoney;
use App\Models\Utilisateur;
use App\Models\VerificationCode;
use App\Models\SessionOmpay;
use App\Models\QRCode;
use App\Models\Portefeuille;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Jobs\SendOtpSmsJob;

class AuthenticationService
{
    public function initiateLogin($numero_telephone)
    {
        // Vérifier si le numéro existe dans Orange Money
        $compte_om = OrangeMoney::where('numero_telephone', $numero_telephone)->first();
        if (!$compte_om) {
            throw new \Exception("Ce numéro n'a pas de compte Orange Money");
        }

        // Générer le code OTP et le token
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        // Sauvegarder le code de vérification
        $verification = VerificationCode::create([
            'numero_telephone' => $numero_telephone,
            'code' => $code,
            'token' => $token,
            'expire_at' => Carbon::now()->addMinutes(15)
        ]);

        // Dans un environnement réel, envoyer le SMS ici
        $lien = env('APP_URL') . "/verify/" . $token;

        // Dispatcher une job pour envoyer le SMS de manière asynchrone
        try {
            SendOtpSmsJob::dispatch($numero_telephone, $code);
        } catch (\Exception $e) {
            \Log::error('AuthenticationService initiateLogin dispatch job error: ' . $e->getMessage(), ['phone' => $numero_telephone]);
            // On ne lance pas d'exception ici pour ne pas bloquer la création du code de vérification;
            // la job pourra être traitée/retryée par le système de queue.
        }

        return [
            'message' => 'Un SMS a été envoyé avec le code de vérification',
            'code' => $code, // En production, ne pas renvoyer le code
            'lien' => $lien
        ];
    }

    public function verifyCode($token, $code)
    {
        $verification = VerificationCode::where('token', $token)
            ->where('code', $code)
            ->where('used', false)
            ->where('expire_at', '>', Carbon::now())
            ->first();

        if (!$verification) {
            throw new \Exception("Code invalide ou expiré");
        }

        // Marquer le code comme utilisé
        $verification->used = true;
        $verification->save();

        // Vérifier si l'utilisateur existe déjà
        $compte_om = OrangeMoney::where('numero_telephone', $verification->numero_telephone)->first();
        $utilisateur = Utilisateur::where('numero_telephone', $verification->numero_telephone)->first();

        if (!$utilisateur) {
            // Vérifier que le compte Orange Money existe toujours
            if (!$compte_om) {
                throw new \Exception("Compte Orange Money introuvable pour ce numéro de téléphone");
            }

            // Si un utilisateur existe déjà avec le même numéro CNI, l'utiliser au lieu de tenter
            // une insertion qui provoquerait une violation de contrainte unique.
            $existingByCni = null;
            if (!empty($compte_om->numero_cni)) {
                $existingByCni = Utilisateur::where('numero_cni', $compte_om->numero_cni)->first();
            }

            if ($existingByCni) {
                // Mettre à jour le numéro de téléphone si nécessaire
                if (empty($existingByCni->numero_telephone) || $existingByCni->numero_telephone !== $verification->numero_telephone) {
                    $existingByCni->update(['numero_telephone' => $verification->numero_telephone]);
                }

                // Assurer l'existence du portefeuille
                $portefeuille = Portefeuille::firstOrCreate([
                    'id_utilisateur' => $existingByCni->id
                ], [
                    'solde' => $compte_om->solde ?? 0,
                    'devise' => 'XOF'
                ]);

                $qrCode = $this->generateUserQRCode($existingByCni);

                return [
                    'status' => 'user_linked',
                    'numero_telephone' => $verification->numero_telephone,
                    'token' => $token,
                    'user' => $existingByCni,
                    'portefeuille' => $portefeuille,
                    'qr_code' => $qrCode
                ];
            }

            // Premier accès, créer automatiquement l'utilisateur
            $utilisateur = Utilisateur::create([
                'numero_telephone' => $verification->numero_telephone,
                'prenom' => $compte_om->prenom ?? null,
                'nom' => $compte_om->nom ?? null,
                'email' => null, // Peut être ajouté plus tard
                'code_pin' => null, // Sera défini lors de la première connexion
                'numero_cni' => $compte_om->numero_cni ?? null,
                'statut_kyc' => 'verifie'
            ]);

            // Créer le portefeuille automatiquement avec le solde du compte Orange Money
            $portefeuille = Portefeuille::create([
                'id_utilisateur' => $utilisateur->id,
                'solde' => $compte_om->solde ?? 0,
                'devise' => 'XOF',
            ]);

            // Générer un QR code pour l'utilisateur
            $qrCode = $this->generateUserQRCode($utilisateur);

            return [
                'status' => 'user_created',
                'numero_telephone' => $verification->numero_telephone,
                'token' => $token,
                'user' => $utilisateur,
                'portefeuille' => $portefeuille,
                'qr_code' => $qrCode
            ];
        }

        // Créer une session
        $session = $this->createSession($utilisateur);

        return [
            'status' => 'logged_in',
            'session_token' => $session->token,
            'refresh_token' => $session->refresh_token,
            'user' => $utilisateur
        ];
    }

    public function createAccount($numero_telephone, $code_pin, $token)
    {
        // Vérifier le token de vérification
        $verification = VerificationCode::where('token', $token)
            ->where('numero_telephone', $numero_telephone)
            ->where('used', true)
            ->first();

        if (!$verification) {
            throw new \Exception("Token invalide");
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = Utilisateur::where('numero_telephone', $numero_telephone)->first();
        if ($existingUser) {
            throw new \Exception("Un compte utilisateur existe déjà pour ce numéro de téléphone");
        }

        // Récupérer les informations Orange Money
        $compte_om = OrangeMoney::where('numero_telephone', $numero_telephone)->first();
        if (!$compte_om) {
            throw new \Exception("Compte Orange Money non trouvé");
        }

        // Créer l'utilisateur
        $utilisateur = Utilisateur::create([
            'numero_telephone' => $numero_telephone,
            'prenom' => $compte_om->prenom,
            'nom' => $compte_om->nom,
            'email' => null, // Peut être ajouté plus tard
            'code_pin' => Hash::make($code_pin),
            'numero_cni' => $compte_om->numero_cni,
            'statut_kyc' => 'verifie'
        ]);

        // Générer un QR code pour l'utilisateur
        $qrCode = $this->generateUserQRCode($utilisateur);

        // Créer une session
        $session = $this->createSession($utilisateur);

        return [
            'session_token' => $session->token,
            'refresh_token' => $session->refresh_token,
            'user' => $utilisateur,
            'qr_code' => $qrCode
        ];
    }

    private function createSession($utilisateur)
    {
        return SessionOmpay::create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'refresh_token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);
    }

    private function generateUserQRCode($utilisateur)
    {
        if (!$utilisateur) {
            throw new \Exception('Impossible de générer un QR code: utilisateur introuvable');
        }

        // Vérifier si un QR code valide existe déjà pour cet utilisateur
        $existingQr = QRCode::where('id_utilisateur', $utilisateur->id)
            ->where('utilise', false)
            ->where('date_expiration', '>', Carbon::now())
            ->first();

        if ($existingQr) {
            return [
                'id' => $existingQr->id,
                'data' => $existingQr->generer(),
                'expires_at' => $existingQr->date_expiration
            ];
        }

        // Créer un QR code personnel pour l'utilisateur
        $qrCode = QRCode::create([
            'id_utilisateur' => $utilisateur->id,
            'donnees' => json_encode([
                'type' => 'user_profile',
                'user_id' => $utilisateur->id,
                'numero_telephone' => $utilisateur->numero_telephone ?? null,
                'nom' => $utilisateur->nom ?? null,
                'prenom' => $utilisateur->prenom ?? null,
            ]),
            'montant' => null, // Pas de montant pour un QR code utilisateur
            'date_generation' => Carbon::now(),
            'date_expiration' => Carbon::now()->addYears(10), // QR code valide longtemps
            'utilise' => false,
        ]);

        return [
            'id' => $qrCode->id,
            'data' => $qrCode->generer(),
            'expires_at' => $qrCode->date_expiration
        ];
    }

    public function login($numero_telephone, $code_pin)
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = Utilisateur::where('numero_telephone', $numero_telephone)->first();
        if (!$utilisateur) {
            throw new \Exception("Utilisateur non trouvé");
        }

        // Si l'utilisateur n'a pas de code PIN défini (premier accès), permettre la connexion sans vérification
        if ($utilisateur->code_pin === null) {
            // Définir le code PIN pour le premier accès
            $utilisateur->update(['code_pin' => Hash::make($code_pin)]);

            // Créer une session
            $session = $this->createSession($utilisateur);

            // Générer ou récupérer le QR code
            $qrCode = $this->generateUserQRCode($utilisateur);

            return [
                'session_token' => $session->token,
                'refresh_token' => $session->refresh_token,
                'user' => $utilisateur,
                'qr_code' => $qrCode,
                'first_login' => true
            ];
        }

        // Vérifier le code PIN pour les connexions suivantes
        if (!Hash::check($code_pin, $utilisateur->code_pin)) {
            throw new \Exception("Code PIN incorrect");
        }

        // Créer une session
        $session = $this->createSession($utilisateur);

        // Générer ou récupérer le QR code
        $qrCode = $this->generateUserQRCode($utilisateur);

        return [
            'session_token' => $session->token,
            'refresh_token' => $session->refresh_token,
            'user' => $utilisateur,
            'qr_code' => $qrCode,
            'first_login' => false
        ];
    }

    public function logout($request)
    {
        $session = $request->attributes->get('session');
        if ($session) {
            $session->delete();
        }

        return [
            'message' => 'Déconnexion réussie'
        ];
    }
}