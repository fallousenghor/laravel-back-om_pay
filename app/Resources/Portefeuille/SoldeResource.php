<?php

namespace App\Resources\Portefeuille;

use App\DTOs\Portefeuille\SoldeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoldeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SoldeDTO $this->resource */
        return [
            'success' => true,
            'data' => $this->resource->solde
        ];
    }
}