<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\InitierPaiementDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InitierPaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var InitierPaiementDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idPaiement' => $this->resource->idPaiement,
                'statut' => $this->resource->statut,
                'marchand' => $this->resource->marchand,
                'montant' => $this->resource->montant,
                'frais' => $this->resource->frais,
                'montantTotal' => $this->resource->montantTotal,
                'dateExpiration' => $this->resource->dateExpiration,
            ],
            'message' => 'Veuillez confirmer le paiement avec votre code PIN'
        ];
    }
}