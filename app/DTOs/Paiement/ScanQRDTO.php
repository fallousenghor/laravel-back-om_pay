<?php

namespace App\DTOs\Paiement;

class ScanQRDTO
{
    public string $idScan;
    public array $marchand;
    public int $montant;
    public string $devise;
    public string $dateExpiration;
    public bool $valide;

    public function __construct(
        string $idScan,
        array $marchand,
        int $montant,
        string $devise,
        string $dateExpiration,
        bool $valide
    ) {
        $this->idScan = $idScan;
        $this->marchand = $marchand;
        $this->montant = $montant;
        $this->devise = $devise;
        $this->dateExpiration = $dateExpiration;
        $this->valide = $valide;
    }
}