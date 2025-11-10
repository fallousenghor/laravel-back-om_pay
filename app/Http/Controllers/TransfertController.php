<?php

namespace App\Http\Controllers;

use App\Interfaces\TransfertServiceInterface;
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


    /**
     * @OA\Post(
     *     path="/transfert/initier",
     *     summary="Initier un transfert",
     *     description="Crée une demande de transfert d'argent",
     *     tags={"Transferts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroTelephoneDestinataire","montant"},
     *             @OA\Property(property="numeroTelephoneDestinataire", type="string", example="+221701234567"),
     *             @OA\Property(property="montant", type="number", format="float", example=5000.00),
     *             @OA\Property(property="description", type="string", example="Paiement loyer", description="Description optionnelle")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert initié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idTransfert", type="string"),
     *                 @OA\Property(property="montant", type="number", format="float"),
     *                 @OA\Property(property="frais", type="number", format="float"),
     *                 @OA\Property(property="statut", type="string", example="EN_ATTENTE")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function initierTransfert(InitierTransfertRequest $request)
    {
        $utilisateur = $request->user();
        $data = $request->validated();
        // Map the field names to match what the service expects
        $data['telephoneDestinataire'] = $data['numeroTelephoneDestinataire'];
        $data['note'] = $data['description'] ?? $data['note'] ?? null;
        $result = $this->transfertService->initierTransfert($utilisateur, $data);
        return $this->responseFromResult($result);
    }

    /**
     * @OA\Post(
     *     path="/transfert/{idTransfert}/confirmer",
     *     summary="Confirmer un transfert",
     *     description="Confirme et exécute un transfert en attente",
     *     tags={"Transferts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="idTransfert",
     *         in="path",
     *         description="ID du transfert à confirmer",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"codePin"},
     *             @OA\Property(property="codePin", type="string", example="1234", description="Code PIN de l'utilisateur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert confirmé et exécuté",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idTransfert", type="string"),
     *                 @OA\Property(property="statut", type="string", example="REUSSI"),
     *                 @OA\Property(property="dateExecution", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="PIN incorrect ou transfert déjà traité",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function confirmerTransfert(ConfirmerTransfertRequest $request, $idTransfert)
    {
        $utilisateur = $request->user();
        $result = $this->transfertService->confirmerTransfert($utilisateur, $idTransfert, $request->codePin);
        return $this->responseFromResult($result);
    }

    /**
     * @OA\Delete(
     *     path="/transfert/{idTransfert}/annuler",
     *     summary="Annuler un transfert",
     *     description="Annule un transfert en attente",
     *     tags={"Transferts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="idTransfert",
     *         in="path",
     *         description="ID du transfert à annuler",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idTransfert", type="string"),
     *                 @OA\Property(property="statut", type="string", example="ANNULE"),
     *                 @OA\Property(property="dateAnnulation", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Transfert ne peut pas être annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Transfert déjà traité")
     *         )
     *     )
     * )
     */
    public function annulerTransfert(Request $request, $idTransfert)
    {
        $utilisateur = $request->user();
        $result = $this->transfertService->annulerTransfert($utilisateur, $idTransfert);
        return $this->responseFromResult($result);
    }
}
