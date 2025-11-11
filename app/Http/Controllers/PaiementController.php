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


    /**
     * @OA\Post(
     *     path="/paiement/scanner-qr",
     *     summary="Scanner un QR code",
     *     description="Scanne et décode un QR code de paiement marchand",
     *     tags={"Paiements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"donneesQR"},
     *             @OA\Property(property="donneesQR", type="string", example="QR_CODE_DATA_HERE", description="Données encodées dans le QR code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code scanné avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="marchand", type="object"),
     *                 @OA\Property(property="montant", type="number", format="float"),
     *                 @OA\Property(property="reference", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="QR code invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="QR code invalide")
     *         )
     *     )
     * )
     */
    public function scannerQR(ScannerQRRequest $request)
    {
        $result = $this->paiementService->scannerQR($request->donneesQR);
        return $this->responseFromResult($result);
    }

    /**
     * @OA\Post(
     *     path="/paiement/saisir-code",
     *     summary="Saisir un code de paiement",
     *     description="Saisit manuellement un code de paiement marchand",
     *     tags={"Paiements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","montant"},
     *             @OA\Property(property="code", type="string", example="PAY123456", description="Code de paiement fourni par le marchand"),
     *             @OA\Property(property="montant", type="number", format="float", example=5000, description="Montant du paiement en XOF")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code de paiement validé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idPaiement", type="string", example="OM20251111131953ABC123"),
     *                 @OA\Property(property="marchand", type="object",
     *                     @OA\Property(property="idMarchand", type="string"),
     *                     @OA\Property(property="nom", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(property="montant", type="number", format="float", example=5000),
     *                 @OA\Property(property="devise", type="string", example="XOF"),
     *                 @OA\Property(property="dateExpiration", type="string", format="date-time"),
     *                 @OA\Property(property="valide", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Code de paiement invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code de paiement invalide")
     *         )
     *     )
     * )
     */
    public function saisirCode(SaisirCodeRequest $request)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->saisirCode($utilisateur, $request->code, $request->montant);
        return $this->responseFromResult($result);
    }


    /**
     * @OA\Post(
     *     path="/paiement/{idPaiement}/confirmer",
     *     summary="Confirmer un paiement",
     *     description="Confirme et exécute un paiement en attente",
     *     tags={"Paiements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="idPaiement",
     *         in="path",
     *         description="ID du paiement à confirmer",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"codePin"},
     *             @OA\Property(property="montant", type="number", format="float", example=2000, description="Montant du paiement (optionnel pour vérification)"),
     *             @OA\Property(property="codePin", type="string", example="1234", description="Code PIN de l'utilisateur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement confirmé et exécuté",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idPaiement", type="string"),
     *                 @OA\Property(property="statut", type="string", example="REUSSI"),
     *                 @OA\Property(property="dateExecution", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="PIN incorrect ou paiement déjà traité",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function confirmerPaiement(ConfirmerPaiementRequest $request, $idPaiement)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->confirmerPaiement($utilisateur, $idPaiement, $request->codePin, $request->montant);
        return $this->responseFromResult($result);
    }

    /**
     * @OA\Delete(
     *     path="/paiement/{idPaiement}/annuler",
     *     summary="Annuler un paiement",
     *     description="Annule un paiement en attente",
     *     tags={"Paiements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="idPaiement",
     *         in="path",
     *         description="ID du paiement à annuler",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="idPaiement", type="string"),
     *                 @OA\Property(property="statut", type="string", example="ANNULE"),
     *                 @OA\Property(property="dateAnnulation", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Paiement ne peut pas être annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement déjà traité")
     *         )
     *     )
     * )
     */
    public function annulerPaiement(Request $request, $idPaiement)
    {
        $utilisateur = $request->user();
        $result = $this->paiementService->annulerPaiement($utilisateur, $idPaiement);
        return $this->responseFromResult($result);
    }
}
