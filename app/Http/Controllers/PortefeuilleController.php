<?php

namespace App\Http\Controllers;

use App\Interfaces\PortefeuilleServiceInterface;
use App\Http\Requests\HistoriqueTransactionsRequest;
use Illuminate\Http\Request;

class PortefeuilleController extends Controller
{
    protected $portefeuilleService;

    public function __construct(PortefeuilleServiceInterface $portefeuilleService)
    {
        $this->portefeuilleService = $portefeuilleService;
    }

    /**
     * @OA\Get(
     *     path="/{numeroCompte}/portefeuille/solde",
     *     summary="Consulter le solde",
     *     description="Récupère le solde actuel du portefeuille OM Pay",
     *     tags={"Portefeuille"},
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
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", format="float", example=15000.50),
     *                 @OA\Property(property="devise", type="string", example="XOF")
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
    public function consulterSolde(Request $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $result = $this->portefeuilleService->consulterSolde($utilisateur);

        return $this->responseFromResult($result);
    }

    /**
     * @OA\Get(
     *     path="/{numeroCompte}/portefeuille/transactions",
     *     summary="Historique des transactions",
     *     description="Récupère l'historique des transactions avec filtres optionnels",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numeroCompte",
     *         in="path",
     *         description="Numéro de compte de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="7735434534")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limite",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de transaction (transfert, paiement, etc.)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dateDebut",
     *         in="query",
     *         description="Date de début (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="dateFin",
     *         in="query",
     *         description="Date de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut de la transaction",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transactions", type="array", @OA\Items(
     *                     @OA\Property(property="idTransaction", type="string", example="01HN1234567890ABCDEF"),
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
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="pageActuelle", type="integer", example=1),
     *                     @OA\Property(property="totalPages", type="integer", example=5),
     *                     @OA\Property(property="totalElements", type="integer", example=100),
     *                     @OA\Property(property="elementsParPage", type="integer", example=20)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function historiqueTransactions(HistoriqueTransactionsRequest $request, $numeroCompte)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $page = $request->get('page', 1);
        $limite = $request->get('limite', 20);

        $filters = $request->only(['type', 'dateDebut', 'dateFin', 'statut']);

        $result = $this->portefeuilleService->historiqueTransactions($utilisateur, $filters, $page, $limite);

        return $this->responseFromResult($result);
    }

    /**
     * @OA\Get(
     *     path="/{numeroCompte}/portefeuille/transactions/{idTransaction}",
     *     summary="Détails d'une transaction",
     *     description="Récupère les détails d'une transaction spécifique",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numeroCompte",
     *         in="path",
     *         description="Numéro de compte de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="7735434534")
     *     ),
     *     @OA\Parameter(
     *         name="idTransaction",
     *         in="path",
     *         description="ID de la transaction",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la transaction récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="montant", type="number", format="float"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="statut", type="string"),
     *                 @OA\Property(property="date", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Transaction non trouvée")
     *         )
     *     )
     * )
     */
    public function detailsTransaction(Request $request, $numeroCompte, $idTransaction)
    {
        // Vérifier que le numéro de compte correspond à l'utilisateur connecté
        $utilisateur = $request->user();
        if ($utilisateur->numero_telephone !== $numeroCompte) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de compte invalide'
            ], 403);
        }

        $result = $this->portefeuilleService->detailsTransaction($utilisateur, $idTransaction);

        return $this->responseFromResult($result);
    }
}
