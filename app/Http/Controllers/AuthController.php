<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Étape 1: Initier la création de compte avec le numéro de téléphone
     */
    public function initiateRegistration(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'numero_telephone' => 'required|string|regex:/^\+221[7][0-9]{8}$/'
            ]);

            $result = $this->authService->initiateLogin($request->numero_telephone);

            return response()->json([
                'success' => true,
                'message' => 'Code de vérification envoyé avec succès',
                'data' => [
                    'token' => explode('/', $result['lien'])[4],
                    'code_otp' => $result['code'], // À supprimer en production
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Étape 2: Vérifier le code OTP
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'code' => 'required|string|size:6'
            ]);

            $result = $this->authService->verifyCode(
                $request->token,
                $request->code
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Étape 3: Créer le compte avec le code PIN
     */
    public function createAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'numero_telephone' => 'required|string|regex:/^\+221[7][0-9]{8}$/',
                'token' => 'required|string',
                'code_pin' => 'required|string|size:4|regex:/^\d{4}$/'
            ]);

            $result = $this->authService->createAccount(
                $request->numero_telephone,
                $request->code_pin,
                $request->token
            );

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // 1.1 Créer Compte (alias pour initier-inscription)
    public function creerCompte(InitierInscriptionRequest $request)
    {
        return $this->initierInscription($request);
    }

    // 1.2 Vérification OTP (pour connexion existante)
    public function verificationOtp(VerificationOtpRequest $request)
    {
        $result = $this->authService->verificationOtp($request->numeroTelephone, $request->codeOTP);
        return $this->responseFromResult($result);
    }

    /**
     * Connexion avec numéro de téléphone et code PIN
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'numero_telephone' => 'required|string|regex:/^\+221[7][0-9]{8}$/',
                'code_pin' => 'required|string|size:4|regex:/^\d{4}$/'
            ]);

            $result = $this->authService->login($request->numero_telephone, $request->code_pin);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->logout($request);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // 1.6 Consulter Profil
    public function consulterProfil(Request $request)
    {
        $utilisateur = $request->user();
        $result = $this->userService->consulterProfil($utilisateur);
        return $this->responseFromResult($result);
    }

    // 1.7 Mettre à jour Profil
    public function mettreAJourProfil(MettreAJourProfilRequest $request)
    {
        $utilisateur = $request->user();

        // Vérifier le PIN avant mise à jour
        if (!$this->securityService->verifierPin($utilisateur, $request->codePin)) {
            return $this->errorResponse('USER_006', 'PIN incorrect', [], 401);
        }

        $result = $this->userService->mettreAJourProfil($utilisateur, $request->only(['prenom', 'nom', 'email']));
        return $this->responseFromResult($result);
    }

    // 1.8 Changer le Code PIN
    public function changerPin(ChangerPinRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->securityService->changerPin($utilisateur, $request->ancienPin, $request->nouveauPin);
        return $this->responseFromResult($result);
    }

    // 1.9 Activer la Biométrie
    public function activerBiometrie(ActiverBiometrieRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->securityService->activerBiometrie($utilisateur, $request->codePin, $request->jetonBiometrique);
        return $this->responseFromResult($result);
    }
}
