<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\ConfirmerPaiementDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmerPaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ConfirmerPaiementDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idTransaction' => $this->resource->idTransaction,
                'statut' => $this->resource->statut,
                'marchand' => $this->resource->marchand,
                'montant' => $this->resource->montant,
                'dateTransaction' => $this->resource->dateTransaction,
                'reference' => $this->resource->reference,
                'recu' => $this->resource->recu,
            ],
            'message' => 'Paiement effectué avec succès'
        ];
    }
}