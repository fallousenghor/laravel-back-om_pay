<?php

namespace App\Traits;

use App\DTOs\Portefeuille\SoldeDTO;
use App\DTOs\Portefeuille\HistoriqueTransactionsDTO;
use App\DTOs\Portefeuille\DetailsTransactionDTO;
use App\Resources\Portefeuille\SoldeResource;
use App\Resources\Portefeuille\HistoriqueTransactionsResource;
use App\Resources\Portefeuille\DetailsTransactionResource;
use Illuminate\Http\JsonResponse;

trait PortefeuilleResponseTrait
{
    /**
     * Formatte la réponse pour consulter le solde
     */
    protected function soldeResponse(SoldeDTO $dto): JsonResponse
    {
        return (new SoldeResource($dto))->response();
    }

    /**
     * Formatte la réponse pour l'historique des transactions
     */
    protected function historiqueTransactionsResponse(HistoriqueTransactionsDTO $dto): JsonResponse
    {
        return (new HistoriqueTransactionsResource($dto))->response();
    }

    /**
     * Formatte la réponse pour les détails d'une transaction
     */
    protected function detailsTransactionResponse(DetailsTransactionDTO $dto): JsonResponse
    {
        return (new DetailsTransactionResource($dto))->response();
    }

    /**
     * Formatte une réponse d'erreur pour le portefeuille
     */
    protected function portefeuilleErrorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'WALLET_' . strtoupper(substr($message, 0, 3)),
                'message' => __('messages.fr.errors.' . $this->getPortefeuilleErrorKey($message))
            ]
        ], $statusCode);
    }

    /**
     * Map error messages to translation keys for portefeuille
     */
    private function getPortefeuilleErrorKey(string $message): string
    {
        $errorMap = [
            'Token invalide' => 'invalid_token',
            'Transaction non trouvée' => 'not_found',
        ];

        return $errorMap[$message] ?? 'server_error';
    }
}