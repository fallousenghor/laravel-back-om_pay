<?php

namespace App\DTOs\Portefeuille;

class SoldeDTO
{
    public float $solde;

    public function __construct(float $solde)
    {
        $this->solde = $solde;
    }
}