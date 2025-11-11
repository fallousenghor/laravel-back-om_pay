<?php

namespace App\Resources\Paiement;

use App\DTOs\Paiement\CategoriesDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var CategoriesDTO $this->resource */
        return [
            'success' => true,
            'data' => [
                'categories' => $this->resource->categories
            ]
        ];
    }
}