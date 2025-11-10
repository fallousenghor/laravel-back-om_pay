<?php

namespace App\Http\Controllers;

use App\Interfaces\HistoriqueServiceInterface;
use App\Http\Requests\RechercherHistoriqueRequest;

class HistoriqueController extends Controller
{
    protected $historiqueService;

    public function __construct(HistoriqueServiceInterface $historiqueService)
    {
        $this->historiqueService = $historiqueService;
    }

    // 6.1 Rechercher dans l'Historique
    public function rechercher(RechercherHistoriqueRequest $request)
    {
        $utilisateur = $request->user();
        $filters = $request->validated();

        $result = $this->historiqueService->rechercher($utilisateur, $filters);
        return $this->responseFromResult($result);
    }
}
