<?php

namespace App\Http\Controllers;

use App\Interfaces\TransfertServiceInterface;
use App\Http\Requests\VerifierDestinataireRequest;
use App\Http\Requests\InitierTransfertRequest;
use App\Http\Requests\ConfirmerTransfertRequest;
use Illuminate\Http\Request;

class TransfertController extends Controller
{
    protected $transfertService;

    public function __construct(TransfertServiceInterface $transfertService)
    {
        $this->transfertService = $transfertService;
    }

    // 3.1 Vérifier un Destinataire
    public function verifierDestinataire(VerifierDestinataireRequest $request)
    {
        $result = $this->transfertService->verifierDestinataire($request->numeroTelephone);
        return $this->responseFromResult($result);
    }

    // 3.2 Initier un Transfert
    public function initierTransfert(InitierTransfertRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->transfertService->initierTransfert($utilisateur, $request->validated());
        return $this->responseFromResult($result);
    }

    // 3.3 Confirmer un Transfert
    public function confirmerTransfert(ConfirmerTransfertRequest $request, $idTransfert)
    {
        $utilisateur = $request->user();
        $result = $this->transfertService->confirmerTransfert($utilisateur, $idTransfert, $request->codePin);
        return $this->responseFromResult($result);
    }

    // 3.4 Annuler un Transfert
    public function annulerTransfert(Request $request, $idTransfert)
    {
        $utilisateur = $request->user();
        $result = $this->transfertService->annulerTransfert($utilisateur, $idTransfert);
        return $this->responseFromResult($result);
    }
}
