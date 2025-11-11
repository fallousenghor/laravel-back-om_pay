<?php

namespace App\Http\Controllers;

use App\Interfaces\AuthenticationServiceInterface;
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

    public function __construct(AuthenticationServiceInterface $authService)
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
                'message' => __('messages.fr.auth.initiate_registration.success'),
                'data' => [
                    'token' => explode('/', $result['lien'])[4],
                    'code_otp' => $result['code'], // À supprimer en production
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.fr.auth.initiate_registration.phone_invalid')
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

            // Normaliser la réponse pour ne renvoyer que le token et le numéro de téléphone
            $token = $result['session_token'] ?? $result['token'] ?? null;

            $phone = null;
            if (isset($result['numero_telephone'])) {
                $phone = $result['numero_telephone'];
            } elseif (isset($result['user'])) {
                $user = $result['user'];
                if (is_object($user)) {
                    $phone = $user->numero_telephone ?? null;
                } elseif (is_array($user)) {
                    $phone = $user['numero_telephone'] ?? null;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'numero_telephone' => $phone,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.fr.auth.verify_otp.invalid')
            ], 400);
        }
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
    *             @OA\Property(property="data", type="object",
    *                 @OA\Property(property="session_token", type="string", example="PMOfGqub4qI1LRynqATixgXiug0PnrCjgEF7VTJqOcazKv0XrFTEnLANQJkhnBbK")
    *             )
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

            // Retourner uniquement le token de session (pas l'objet utilisateur)
            return response()->json([
                'success' => true,
                'message' => __('messages.fr.auth.login.success'),
                'data' => [
                    'session_token' => $result['session_token'] ?? null
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.fr.auth.login.invalid_credentials')
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
                'message' => __('messages.fr.auth.logout.success')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.fr.errors.server_error')
            ], 400);
        }
    }

}
