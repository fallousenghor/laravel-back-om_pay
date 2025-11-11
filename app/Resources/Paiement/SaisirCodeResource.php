<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\SaisirCodeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaisirCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SaisirCodeDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idCode' => $this->resource->idCode,
                'marchand' => $this->resource->marchand,
                'montant' => $this->resource->montant,
                'devise' => $this->resource->devise,
                'dateExpiration' => $this->resource->dateExpiration,
                'valide' => $this->resource->valide,
            ],
            'message' => 'Code validé avec succès'
        ];
    }
}