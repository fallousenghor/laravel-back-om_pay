<?php

namespace App\Services;

use App\DTOs\InitiateLoginDTO;
use App\DTOs\VerifyCodeDTO;
use App\DTOs\LoginDTO;
use App\DTOs\CreateAccountDTO;
use App\Interfaces\AuthenticationServiceInterface;
use App\Interfaces\UtilisateurRepositoryInterface;
use App\Interfaces\OrangeMoneyRepositoryInterface;
use App\Interfaces\VerificationCodeRepositoryInterface;
use App\Interfaces\SessionOmpayRepositoryInterface;
use App\Interfaces\QRCodeRepositoryInterface;
use App\Interfaces\PortefeuilleRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Jobs\SendOtpSmsJob;
use Exception;

class AuthenticationService implements AuthenticationServiceInterface
{
    private UtilisateurRepositoryInterface $utilisateurRepository;
    private OrangeMoneyRepositoryInterface $orangeMoneyRepository;
    private VerificationCodeRepositoryInterface $verificationCodeRepository;
    private SessionOmpayRepositoryInterface $sessionRepository;
    private QRCodeRepositoryInterface $qrCodeRepository;
    private PortefeuilleRepositoryInterface $portefeuilleRepository;

    public function __construct(
        UtilisateurRepositoryInterface $utilisateurRepository,
        OrangeMoneyRepositoryInterface $orangeMoneyRepository,
        VerificationCodeRepositoryInterface $verificationCodeRepository,
        SessionOmpayRepositoryInterface $sessionRepository,
        QRCodeRepositoryInterface $qrCodeRepository,
        PortefeuilleRepositoryInterface $portefeuilleRepository
    ) {
        $this->utilisateurRepository = $utilisateurRepository;
        $this->orangeMoneyRepository = $orangeMoneyRepository;
        $this->verificationCodeRepository = $verificationCodeRepository;
        $this->sessionRepository = $sessionRepository;
        $this->qrCodeRepository = $qrCodeRepository;
        $this->portefeuilleRepository = $portefeuilleRepository;
    }

    public function initiateLogin(string $numeroTelephone): InitiateLoginDTO
    {
        // Business logic: Vérifier si le numéro existe dans Orange Money
        $compteOm = $this->orangeMoneyRepository->findByPhoneNumber($numeroTelephone);
        if (!$compteOm) {
            throw new Exception("Ce numéro n'a pas de compte Orange Money");
        }

        // Business logic: Générer le code OTP et le token
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        // Business logic: Sauvegarder le code de vérification
        $this->verificationCodeRepository->create([
            'numero_telephone' => $numeroTelephone,
            'code' => $code,
            'token' => $token,
            'expire_at' => Carbon::now()->addMinutes(15)
        ]);

        // Business logic: Générer le lien de vérification
        $lien = env('APP_URL') . "/verify/" . $token;

        // Business logic: Dispatcher une job pour envoyer le SMS
        try {
            SendOtpSmsJob::dispatch($numeroTelephone, $code);
        } catch (Exception $e) {
            \Log::error('AuthenticationService initiateLogin dispatch job error: ' . $e->getMessage(), ['phone' => $numeroTelephone]);
            // On ne lance pas d'exception ici pour ne pas bloquer la création du code de vérification;
            // la job pourra être traitée/retryée par le système de queue.
        }

        return new InitiateLoginDTO($numeroTelephone, $code, $token, $lien);
    }

    public function verifyCode(string $token, string $code): VerifyCodeDTO
    {
        // Business logic: Trouver et valider le code de vérification
        $verification = $this->verificationCodeRepository->findValidByTokenAndCode($token, $code);
        if (!$verification) {
            throw new Exception("Code invalide ou expiré");
        }

        // Business logic: Marquer le code comme utilisé
        $this->verificationCodeRepository->markAsUsed($verification);

        // Business logic: Gérer les différents scénarios d'utilisateur
        return $this->handleUserVerification($verification, $token);
    }

    private function handleUserVerification($verification, string $token): VerifyCodeDTO
    {
        $phoneNumber = $verification->numero_telephone;
        $compteOm = $this->orangeMoneyRepository->findByPhoneNumber($phoneNumber);
        $utilisateur = $this->utilisateurRepository->findByPhoneNumber($phoneNumber);

        if (!$utilisateur) {
            return $this->handleNewUser($compteOm, $verification, $token);
        }

        // Business logic: Créer une session pour l'utilisateur existant
        $session = $this->sessionRepository->create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);

        return new VerifyCodeDTO(
            'logged_in',
            null,
            null,
            $utilisateur,
            null,
            null,
            $session->token
        );
    }

    private function handleNewUser($compteOm, $verification, string $token): VerifyCodeDTO
    {
        if (!$compteOm) {
            throw new Exception("Compte Orange Money introuvable pour ce numéro de téléphone");
        }

        // Business logic: Gérer le cas où un utilisateur existe déjà avec le même CNI
        $existingByCni = $this->findExistingUserByCni($compteOm);
        if ($existingByCni) {
            return $this->handleExistingUserByCni($existingByCni, $compteOm, $verification, $token);
        }

        // Business logic: Créer un nouvel utilisateur
        return $this->createNewUser($compteOm, $verification, $token);
    }

    private function findExistingUserByCni($compteOm)
    {
        if (empty($compteOm->numero_cni)) {
            return null;
        }
        return $this->utilisateurRepository->findByCni($compteOm->numero_cni);
    }

    private function handleExistingUserByCni($existingByCni, $compteOm, $verification, string $token): VerifyCodeDTO
    {
        // Business logic: Mettre à jour le numéro de téléphone si nécessaire
        if (empty($existingByCni->numero_telephone) || $existingByCni->numero_telephone !== $verification->numero_telephone) {
            $this->utilisateurRepository->update($existingByCni, ['numero_telephone' => $verification->numero_telephone]);
        }

        // Business logic: Assurer l'existence du portefeuille
        $portefeuille = $this->portefeuilleRepository->findOrCreateByUserId($existingByCni->id, [
            'solde' => $compteOm->solde ?? 0,
            'devise' => 'XOF'
        ]);

        $qrCode = $this->generateUserQRCode($existingByCni);

        return new VerifyCodeDTO(
            'user_linked',
            $verification->numero_telephone,
            $token,
            $existingByCni,
            $portefeuille,
            $qrCode
        );
    }

    private function createNewUser($compteOm, $verification, string $token): VerifyCodeDTO
    {
        // Business logic: Créer un nouvel utilisateur
        $utilisateur = $this->utilisateurRepository->create([
            'numero_telephone' => $verification->numero_telephone,
            'prenom' => $compteOm->prenom ?? null,
            'nom' => $compteOm->nom ?? null,
            'email' => null,
            'code_pin' => null,
            'numero_cni' => $compteOm->numero_cni ?? null,
            'statut_kyc' => 'verifie'
        ]);

        // Business logic: Créer le portefeuille
        $portefeuille = $this->portefeuilleRepository->findOrCreateByUserId($utilisateur->id, [
            'solde' => $compteOm->solde ?? 0,
            'devise' => 'XOF'
        ]);

        // Business logic: Générer un QR code
        $qrCode = $this->generateUserQRCode($utilisateur);

        return new VerifyCodeDTO(
            'user_created',
            $verification->numero_telephone,
            $token,
            $utilisateur,
            $portefeuille,
            $qrCode
        );
    }

    public function createAccount(string $numeroTelephone, string $codePin, string $token): CreateAccountDTO
    {
        // Business logic: Valider le token de vérification
        $verification = $this->verificationCodeRepository->findByTokenAndPhone($token, $numeroTelephone);
        if (!$verification) {
            throw new Exception("Token invalide");
        }

        // Business logic: Vérifier que l'utilisateur n'existe pas déjà
        $existingUser = $this->utilisateurRepository->findByPhoneNumber($numeroTelephone);
        if ($existingUser) {
            throw new Exception("Un compte utilisateur existe déjà pour ce numéro de téléphone");
        }

        // Business logic: Récupérer les informations Orange Money
        $compteOm = $this->orangeMoneyRepository->findByPhoneNumber($numeroTelephone);
        if (!$compteOm) {
            throw new Exception("Compte Orange Money non trouvé");
        }

        // Business logic: Créer l'utilisateur avec PIN hashé
        $utilisateur = $this->utilisateurRepository->create([
            'numero_telephone' => $numeroTelephone,
            'prenom' => $compteOm->prenom,
            'nom' => $compteOm->nom,
            'email' => null,
            'code_pin' => Hash::make($codePin),
            'numero_cni' => $compteOm->numero_cni,
            'statut_kyc' => 'verifie'
        ]);

        // Business logic: Générer un QR code
        $qrCode = $this->generateUserQRCode($utilisateur);

        // Business logic: Créer une session
        $session = $this->sessionRepository->create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);

        return new CreateAccountDTO($session->token, $utilisateur, $qrCode);
    }


    private function generateUserQRCode($utilisateur): array
    {
        // Business logic: Validation de l'utilisateur
        if (!$utilisateur) {
            throw new Exception('Impossible de générer un QR code: utilisateur introuvable');
        }

        // Business logic: Créer un QR code personnel pour l'utilisateur
        $qrCode = $this->qrCodeRepository->create([
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

    public function login(string $numeroTelephone, string $codePin): LoginDTO
    {
        // Business logic: Trouver l'utilisateur
        $utilisateur = $this->utilisateurRepository->findByPhoneNumber($numeroTelephone);
        if (!$utilisateur) {
            throw new Exception("Utilisateur non trouvé");
        }

        // Business logic: Gérer le premier accès (sans PIN défini)
        if ($utilisateur->code_pin === null) {
            return $this->handleFirstLogin($utilisateur, $codePin);
        }

        // Business logic: Vérifier le PIN pour les connexions suivantes
        if (!Hash::check($codePin, $utilisateur->code_pin)) {
            throw new Exception("Code PIN incorrect");
        }

        return $this->createLoginSession($utilisateur, false);
    }

    private function handleFirstLogin($utilisateur, string $codePin): LoginDTO
    {
        // Business logic: Définir le code PIN pour le premier accès
        $this->utilisateurRepository->update($utilisateur, ['code_pin' => Hash::make($codePin)]);

        return $this->createLoginSession($utilisateur, true);
    }

    private function createLoginSession($utilisateur, bool $isFirstLogin): LoginDTO
    {
        // Business logic: Créer une session
        $session = $this->sessionRepository->create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);

        return new LoginDTO($session->token, $utilisateur, $isFirstLogin);
    }

    public function completeLogin($verification): VerifyCodeDTO
    {
        // Business logic: Valider la vérification
        if (!$verification || $verification->used || $verification->expire_at <= Carbon::now()) {
            throw new Exception("Vérification invalide ou expirée");
        }

        // Business logic: Récupérer l'utilisateur
        $utilisateur = $this->utilisateurRepository->findByPhoneNumber($verification->numero_telephone);
        if (!$utilisateur) {
            throw new Exception("Utilisateur non trouvé");
        }

        // Business logic: Créer une session
        $session = $this->sessionRepository->create([
            'utilisateur_id' => $utilisateur->id,
            'token' => Str::random(64),
            'last_activity' => Carbon::now()
        ]);

        // Business logic: Marquer la vérification comme utilisée
        $this->verificationCodeRepository->markAsUsed($verification);

        return new VerifyCodeDTO(
            'logged_in',
            null,
            null,
            $utilisateur,
            null,
            null,
            $session->token
        );
    }

    public function logout($request): array
    {
        // Business logic: Supprimer la session si elle existe
        $session = $request->attributes->get('session');
        if ($session) {
            $this->sessionRepository->delete($session);
        }

        return [
            'message' => 'Déconnexion réussie'
        ];
    }
}