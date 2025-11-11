<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\AnnulerPaiementDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnulerPaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AnnulerPaiementDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idPaiement' => $this->resource->idPaiement,
                'statut' => $this->resource->statut,
                'dateAnnulation' => $this->resource->dateAnnulation,
            ],
            'message' => 'Paiement annulé avec succès'
        ];
    }
}