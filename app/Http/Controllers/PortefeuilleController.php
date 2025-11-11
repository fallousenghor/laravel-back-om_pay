<?php

namespace App\Http\Controllers;

use App\Interfaces\PortefeuilleServiceInterface;
use App\Http\Requests\HistoriqueTransactionsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortefeuilleController extends Controller
{
    protected $portefeuilleService;

    public function __construct(PortefeuilleServiceInterface $portefeuilleService)
    {
        $this->portefeuilleService = $portefeuilleService;
    }

    /**
     * @OA\Get(
     *     path="/portefeuille/solde",
     *     summary="Consulter le solde",
     *     description="Récupère le solde actuel du portefeuille OM Pay",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth":{}}},
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
    public function consulterSolde(Request $request): JsonResponse
    {
        $utilisateur = $request->user();
        $result = $this->portefeuilleService->consulterSolde($utilisateur);

        return $this->responseFromResult($result);
    }

    /**
     * @OA\Get(
     *     path="/portefeuille/transactions",
     *     summary="Historique des transactions",
     *     description="Récupère l'historique des transactions avec filtres optionnels",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth":{}}},
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
     *                 @OA\Property(property="transactions", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function historiqueTransactions(HistoriqueTransactionsRequest $request): JsonResponse
    {
        $utilisateur = $request->user();
        $page = $request->get('page', 1);
        $limite = $request->get('limite', 20);

        $filters = $request->only(['type', 'dateDebut', 'dateFin', 'statut']);

        $result = $this->portefeuilleService->historiqueTransactions($utilisateur, $filters, $page, $limite);

        return $this->responseFromResult($result);
    }

    /**
     * @OA\Get(
     *     path="/portefeuille/transactions/{idTransaction}",
     *     summary="Détails d'une transaction",
     *     description="Récupère les détails d'une transaction spécifique",
     *     tags={"Portefeuille"},
     *     security={{"bearerAuth":{}}},
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
    public function detailsTransaction(Request $request, $idTransaction): JsonResponse
    {
        $utilisateur = $request->user();
        $result = $this->portefeuilleService->detailsTransaction($utilisateur, $idTransaction);

        return $this->responseFromResult($result);
    }
}
