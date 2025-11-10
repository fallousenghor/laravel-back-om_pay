<?php

namespace App\Services;

use App\Models\OrangeMoney;
use App\Models\Utilisateur;
use App\Models\VerificationCode;
use App\Models\SessionOmpay;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
            // Premier accès, retourner les informations pour la création du code PIN
            return [
                'status' => 'first_access',
                'numero_telephone' => $verification->numero_telephone,
                'token' => $token
            ];
        }

        // Créer une session
        $session = $this->createSession($utilisateur);

        return [
            'status' => 'logged_in',
            'session_token' => $session->token,
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

        // Récupérer les informations Orange Money
        $compte_om = OrangeMoney::where('numero_telephone', $numero_telephone)->first();

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

        // Créer une session
        $session = $this->createSession($utilisateur);

        return [
            'session_token' => $session->token,
            'user' => $utilisateur
        ];
    }

    private function createSession($utilisateur)
    {
        return SessionOmpay::create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);
    }
}