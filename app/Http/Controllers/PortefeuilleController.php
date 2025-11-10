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

    // 2.1 Consulter le Solde
    public function consulterSolde(Request $request)
    {
        $utilisateur = $request->user();
        $result = $this->portefeuilleService->consulterSolde($utilisateur);

        return $this->responseFromResult($result);
    }

    // 2.2 Historique des Transactions
    public function historiqueTransactions(HistoriqueTransactionsRequest $request)
    {
        $utilisateur = $request->user();
        $page = $request->get('page', 1);
        $limite = $request->get('limite', 20);

        $filters = $request->only(['type', 'dateDebut', 'dateFin', 'statut']);

        $result = $this->portefeuilleService->historiqueTransactions($utilisateur, $filters, $page, $limite);

        return $this->responseFromResult($result);
    }

    // 2.3 Détails d'une Transaction
    public function detailsTransaction(Request $request, $idTransaction)
    {
        $utilisateur = $request->user();
        $result = $this->portefeuilleService->detailsTransaction($utilisateur, $idTransaction);

        return $this->responseFromResult($result);
    }
}
