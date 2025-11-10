<?php

namespace App\Http\Controllers;

use App\Interfaces\PaiementServiceInterface;
use App\Http\Requests\ScannerQRRequest;
use App\Http\Requests\SaisirCodeRequest;
use App\Http\Requests\InitierPaiementRequest;
use App\Http\Requests\ConfirmerPaiementRequest;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementServiceInterface $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    // 4.1 Lister les Catégories de Marchands
    public function listerCategories(Request $request)
    {
        $result = $this->paiementService->listerCategories();
        return $this->responseFromResult($result);
    }

    // 4.2 Scanner un QR Code
    public function scannerQR(ScannerQRRequest $request)
    {
        $result = $this->paiementService->scannerQR($request->donneesQR);
        return $this->responseFromResult($result);
    }

    // 4.3 Saisir un Code de Paiement
    public function saisirCode(SaisirCodeRequest $request)
    {
        $result = $this->paiementService->saisirCode($request->code);
        return $this->responseFromResult($result);
    }

    // 4.4 Initier un Paiement
    public function initierPaiement(InitierPaiementRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->initierPaiement($utilisateur, $request->validated());
        return $this->responseFromResult($result);
    }

    // 4.5 Confirmer un Paiement
    public function confirmerPaiement(ConfirmerPaiementRequest $request, $idPaiement)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->confirmerPaiement($utilisateur, $idPaiement, $request->codePin);
        return $this->responseFromResult($result);
    }

    // 4.6 Annuler un Paiement
    public function annulerPaiement(Request $request, $idPaiement)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->annulerPaiement($utilisateur, $idPaiement);
        return $this->responseFromResult($result);
    }
}
