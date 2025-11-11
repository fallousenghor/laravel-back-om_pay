<?php

namespace App\DTOs\Paiement;

class SaisirCodeDTO
{
    public string $idCode;
    public array $marchand;
    public ?int $montant;
    public string $devise;
    public ?string $dateExpiration;
    public bool $valide;

    public function __construct(
        string $idCode,
        array $marchand,
        ?int $montant,
        string $devise,
        ?string $dateExpiration,
        bool $valide
    ) {
        $this->idCode = $idCode;
        $this->marchand = $marchand;
        $this->montant = $montant;
        $this->devise = $devise;
        $this->dateExpiration = $dateExpiration;
        $this->valide = $valide;
    }
}