<?php

namespace App\DTOs\Paiement;

class CategoriesDTO
{
    public array $categories;

    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }
}