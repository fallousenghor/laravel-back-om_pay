<?php

namespace App\Resources\Portefeuille;

use App\DTOs\Portefeuille\DetailsTransactionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailsTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var DetailsTransactionDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idTransaction' => $this->resource->idTransaction,
                'type' => $this->resource->type,
                'montant' => $this->resource->montant,
                'devise' => $this->resource->devise,
                'expediteur' => $this->resource->expediteur,
                'destinataire' => $this->resource->destinataire,
                'statut' => $this->resource->statut,
                'dateTransaction' => $this->resource->dateTransaction,
                'reference' => $this->resource->reference,
                'frais' => $this->resource->frais,
                'note' => $this->resource->note,
            ]
        ];
    }
}