<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\ScanQRDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanQRResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ScanQRDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'idScan' => $this->resource->idScan,
                'marchand' => $this->resource->marchand,
                'montant' => $this->resource->montant,
                'devise' => $this->resource->devise,
                'dateExpiration' => $this->resource->dateExpiration,
                'valide' => $this->resource->valide,
            ],
            'message' => 'QR Code scanné avec succès'
        ];
    }
}