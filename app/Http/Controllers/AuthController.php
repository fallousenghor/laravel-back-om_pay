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
                    'token' => basename($result['lien']),
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
    *             @OA\Property(property="data", type="object",
    *                 @OA\Property(property="token", type="string", example="PMOfGqub4qI1LRynqATixgXiug0PnrCjgEF7VTJqOcazKv0XrFTEnLANQJkhnBbK"),
    *                 @OA\Property(property="refresh_token", type="string", example="refresh_token_here")
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

            // Retourner seulement le token et le refresh token
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'token' => $result['session_token'] ?? null,
                    'refresh_token' => $result['refresh_token'] ?? null
                ]
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
    public function consulterProfil(Request $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $result = $this->userService->consulterProfil($utilisateur);
        return $this->responseFromResult($result);
    }

    // 1.7 Mettre à jour Profil
    public function mettreAJourProfil(MettreAJourProfilRequest $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        // Vérifier le PIN avant mise à jour
        if (!$this->securityService->verifierPin($utilisateur, $request->codePin)) {
            return $this->errorResponse('USER_006', 'PIN incorrect', [], 401);
        }

        $result = $this->userService->mettreAJourProfil($utilisateur, $request->only(['prenom', 'nom', 'email']));
        return $this->responseFromResult($result);
    }

    // 1.8 Changer le Code PIN
    public function changerPin(ChangerPinRequest $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $result = $this->securityService->changerPin($utilisateur, $request->ancienPin, $request->nouveauPin);
        return $this->responseFromResult($result);
    }

    // 1.9 Activer la Biométrie
    public function activerBiometrie(ActiverBiometrieRequest $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $result = $this->securityService->activerBiometrie($utilisateur, $request->codePin, $request->jetonBiometrique);
        return $this->responseFromResult($result);
    }

    /**
     * @OA\Get(
     *     path="/{numeroCompte}/dashboard",
     *     summary="Tableau de bord utilisateur",
     *     description="Récupère toutes les informations de l'utilisateur connecté : profil, solde, transactions récentes",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numeroCompte",
     *         in="path",
     *         description="Numéro de compte de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="7735434534")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du tableau de bord récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="utilisateur", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="numero_telephone", type="string"),
     *                     @OA\Property(property="prenom", type="string"),
     *                     @OA\Property(property="nom", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="statut_kyc", type="string"),
     *                     @OA\Property(property="biometrie_activee", type="boolean"),
     *                     @OA\Property(property="date_creation", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="portefeuille", type="object",
     *                     @OA\Property(property="solde", type="number", format="float"),
     *                     @OA\Property(property="devise", type="string")
     *                 ),
     *                 @OA\Property(property="qr_code", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="donnees", type="string"),
     *                     @OA\Property(property="date_generation", type="string", format="date-time"),
     *                     @OA\Property(property="date_expiration", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="transactions_recentes", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01HN1234567890ABCDEF"),
     *                     @OA\Property(property="type", type="string", example="transfert"),
     *                     @OA\Property(property="montant", type="string", example="-5000", description="Montant avec signe +/-"),
     *                     @OA\Property(property="montantNumerique", type="number", format="float", example=5000, description="Montant numérique sans signe"),
     *                     @OA\Property(property="devise", type="string", example="XOF"),
     *                     @OA\Property(property="typeOperation", type="string", enum={"debit", "credit"}, example="debit"),
     *                     @OA\Property(property="expediteur", type="object", nullable=true,
     *                         @OA\Property(property="numeroTelephone", type="string", example="771234567"),
     *                         @OA\Property(property="nom", type="string", example="John Doe")
     *                     ),
     *                     @OA\Property(property="destinataire", type="object", nullable=true,
     *                         @OA\Property(property="numeroTelephone", type="string", example="781234567"),
     *                         @OA\Property(property="nom", type="string", example="Jane Smith")
     *                     ),
     *                     @OA\Property(property="marchand", type="object", nullable=true,
     *                         @OA\Property(property="nom", type="string", example="Boutique Express"),
     *                         @OA\Property(property="categorie", type="string", example="Alimentation")
     *                     ),
     *                     @OA\Property(property="statut", type="string", example="reussi"),
     *                     @OA\Property(property="dateTransaction", type="string", format="date-time"),
     *                     @OA\Property(property="reference", type="string", example="OM20251111131953ABC123"),
     *                     @OA\Property(property="frais", type="number", format="float", example=0)
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function dashboard(Request $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        try {

            // Récupérer les informations de l'utilisateur
            $userData = [
                'id' => $utilisateur->id,
                'numero_compte' => $utilisateur->numero_telephone, // Numéro de compte pour les routes
                'numero_telephone' => $utilisateur->numero_telephone,
                'prenom' => $utilisateur->prenom,
                'nom' => $utilisateur->nom,
                'email' => $utilisateur->email,
                'statut_kyc' => $utilisateur->statut_kyc,
                'biometrie_activee' => $utilisateur->biometrie_activee,
                'date_creation' => $utilisateur->date_creation,
                'derniere_connexion' => $utilisateur->derniere_connexion,
            ];

            // Récupérer les informations du portefeuille
            $portefeuille = $utilisateur->portefeuille;
            $walletData = $portefeuille ? [
                'solde' => $portefeuille->solde,
                'devise' => $portefeuille->devise,
                'derniere_mise_a_jour' => $portefeuille->derniere_mise_a_jour,
            ] : null;

            // Récupérer le QR code actif de l'utilisateur
            $qrCode = $utilisateur->qrCodes()
                ->where('utilise', false)
                ->where('date_expiration', '>', now())
                ->orderBy('date_generation', 'desc')
                ->first();

            $qrCodeData = $qrCode ? [
                'id' => $qrCode->id,
                'donnees' => $qrCode->donnees,
                'date_generation' => $qrCode->date_generation,
                'date_expiration' => $qrCode->date_expiration,
            ] : null;

            // Récupérer les 5 dernières transactions avec logique améliorée (débits/crédits + expéditeur/destinataire)
            $queryExpediteur = \App\Models\Transaction::where('id_utilisateur', $utilisateur->id);
            $queryDestinataire = \App\Models\Transaction::where('type', 'transfert')
                ->whereHas('transfert', function ($q) use ($utilisateur) {
                    $q->where('numero_telephone_destinataire', $utilisateur->numero_telephone);
                });

            $recentTransactions = $queryExpediteur->union($queryDestinataire)
                ->with(['transfert', 'paiement.marchand'])
                ->orderBy('date_transaction', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) use ($utilisateur) {
                    $destinataire = null;
                    $expediteur = null;
                    $marchand = null;
                    $montantAffiche = $transaction->montant;
                    $typeOperation = 'debit'; // Par défaut débit

                    if ($transaction->type === 'transfert') {
                        $transfert = $transaction->transfert;
                        if ($transfert) {
                            // Vérifier si l'utilisateur est l'expéditeur ou le destinataire
                            if ($transfert->id_utilisateur_expediteur === $utilisateur->id) {
                                // L'utilisateur est l'expéditeur -> débit
                                $typeOperation = 'debit';
                                $destinataire = [
                                    'numeroTelephone' => $transfert->numero_telephone_destinataire,
                                    'nom' => $transfert->nom_destinataire,
                                ];
                            } else {
                                // L'utilisateur est le destinataire -> crédit
                                $typeOperation = 'credit';
                                $expediteur = [
                                    'numeroTelephone' => $transfert->numero_telephone_expediteur ?? 'Inconnu',
                                    'nom' => $transfert->nom_expediteur ?? 'Inconnu',
                                ];
                            }
                        } elseif ($transaction->numero_telephone_destinataire && $transaction->nom_destinataire) {
                            // Fallback to transaction fields if transfert relation is null
                            $typeOperation = 'debit'; // Par défaut débit si pas d'info détaillée
                            $destinataire = [
                                'numeroTelephone' => $transaction->numero_telephone_destinataire,
                                'nom' => $transaction->nom_destinataire,
                            ];
                        }
                    } elseif ($transaction->type === 'paiement') {
                        // Les paiements sont toujours des débits pour l'utilisateur
                        $typeOperation = 'debit';
                        $paiement = $transaction->paiement;
                        if ($paiement && $paiement->marchand) {
                            $marchand = [
                                'nom' => $paiement->marchand->nom,
                                'categorie' => $transaction->categorie_marchand ?? 'General',
                            ];
                        } elseif ($transaction->nom_marchand && $transaction->categorie_marchand) {
                            // Fallback to transaction fields if paiement relation is null
                            $marchand = [
                                'nom' => $transaction->nom_marchand,
                                'categorie' => $transaction->categorie_marchand,
                            ];
                        }
                    }

                    // Appliquer le signe selon le type d'opération
                    $montantAffiche = $typeOperation === 'credit' ? '+' . $transaction->montant : '-' . $transaction->montant;

                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'montant' => $montantAffiche,
                        'montantNumerique' => $transaction->montant, // Garder le montant numérique pour les calculs
                        'devise' => $transaction->devise,
                        'typeOperation' => $typeOperation, // 'debit' ou 'credit'
                        'expediteur' => $expediteur,
                        'destinataire' => $destinataire,
                        'marchand' => $marchand,
                        'statut' => $transaction->statut,
                        'dateTransaction' => $transaction->date_transaction->toISOString(),
                        'reference' => $transaction->reference,
                        'frais' => $transaction->frais,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'utilisateur' => $userData,
                    'portefeuille' => $walletData,
                    'qr_code' => $qrCodeData,
                    'transactions_recentes' => $recentTransactions,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données du tableau de bord: ' . $e->getMessage()
            ], 500);
        }
    }
}
