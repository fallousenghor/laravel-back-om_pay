<?php

namespace App\Resources\Portefeuille;

use App\DTOs\Portefeuille\HistoriqueTransactionsDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoriqueTransactionsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var HistoriqueTransactionsDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'transactions' => $this->resource->transactions,
                'pagination' => $this->resource->pagination
            ]
        ];
    }
}