<?php

namespace App\Traits;

use App\DTOs\Paiement\CategoriesDTO;
use App\DTOs\Paiement\ScanQRDTO;
use App\DTOs\Paiement\SaisirCodeDTO;
use App\DTOs\Paiement\InitierPaiementDTO;
use App\DTOs\Paiement\ConfirmerPaiementDTO;
use App\DTOs\Paiement\AnnulerPaiementDTO;
use App\Resources\Paiement\CategoriesResource;
use App\Resources\Paiement\ScanQRResource;
use App\Resources\Paiement\SaisirCodeResource;
use App\Resources\Paiement\InitierPaiementResource;
use App\Resources\Paiement\ConfirmerPaiementResource;
use App\Resources\Paiement\AnnulerPaiementResource;
use Illuminate\Http\JsonResponse;

trait PaiementResponseTrait
{
    /**
     * Formatte la réponse pour lister les catégories
     */
    protected function categoriesResponse(CategoriesDTO $dto): JsonResponse
    {
        return (new CategoriesResource($dto))->response();
    }

    /**
     * Formatte la réponse pour scanner un QR code
     */
    protected function scanQRResponse(ScanQRDTO $dto): JsonResponse
    {
        return (new ScanQRResource($dto))->response();
    }

    /**
     * Formatte la réponse pour saisir un code
     */
    protected function saisirCodeResponse(SaisirCodeDTO $dto): JsonResponse
    {
        return (new SaisirCodeResource($dto))->response();
    }

    /**
     * Formatte la réponse pour initier un paiement
     */
    protected function initierPaiementResponse(InitierPaiementDTO $dto): JsonResponse
    {
        return (new InitierPaiementResource($dto))->response();
    }

    /**
     * Formatte la réponse pour confirmer un paiement
     */
    protected function confirmerPaiementResponse(ConfirmerPaiementDTO $dto): JsonResponse
    {
        return (new ConfirmerPaiementResource($dto))->response();
    }

    /**
     * Formatte la réponse pour annuler un paiement
     */
    protected function annulerPaiementResponse(AnnulerPaiementDTO $dto): JsonResponse
    {
        return (new AnnulerPaiementResource($dto))->response();
    }

    /**
     * Formatte une réponse d'erreur pour les paiements
     */
    protected function paiementErrorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'PAYMENT_' . strtoupper(substr($message, 0, 3)),
                'message' => __('messages.fr.errors.' . $this->getErrorKey($message))
            ]
        ], $statusCode);
    }

    /**
     * Map error messages to translation keys
     */
    private function getErrorKey(string $message): string
    {
        $errorMap = [
            'QR code invalide' => 'qr_invalid',
            'Code de paiement invalide' => 'code_invalid',
            'PIN incorrect' => 'pin_incorrect',
            'Paiement déjà traité' => 'already_processed',
            'Paiement ne peut pas être annulé' => 'cannot_cancel',
        ];

        return $errorMap[$message] ?? 'server_error';
    }
}