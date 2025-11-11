<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * @OA\Info(
 *     title="OM Pay API",
 *     version="1.0.0",
 *     description="API pour l'application OM Pay - Gestion des paiements et transferts"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Serveur de développement"
 * )
 * 
 * @OA\Server(
 *     url="https://om-pay-mk4h.onrender.com/api",
 *     description="Serveur de production"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/initiate",
     *     summary="Initier l'inscription",
     *     description="Envoie un code OTP pour commencer le processus d'inscription",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero_telephone"},
     *             @OA\Property(property="numero_telephone", type="string", example="+221701234567", description="Numéro de téléphone au format +2217XXXXXXXX")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code de vérification envoyé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="abc123"),
     *                 @OA\Property(property="code_otp", type="string", example="123456")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Numéro de téléphone invalide")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/verify-otp",
     *     summary="Vérifier le code OTP",
     *     description="Vérifie le code OTP envoyé par SMS",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","code"},
     *             @OA\Property(property="token", type="string", example="abc123", description="Token reçu lors de l'initiation"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP à 6 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code OTP invalide")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Connexion",
     *     description="Se connecter avec numéro de téléphone et code PIN",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero_telephone","code_pin"},
     *             @OA\Property(property="numero_telephone", type="string", example="+221701234567"),
     *             @OA\Property(property="code_pin", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Numéro ou PIN incorrect")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Déconnexion",
     *     description="Se déconnecter de l'application",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur lors de la déconnexion",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
