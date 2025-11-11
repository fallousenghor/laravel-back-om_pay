<?php

namespace App\DTOs\Paiement;

class InitierPaiementDTO
{
    public string $idPaiement;
    public string $statut;
    public array $marchand;
    public float $montant;
    public float $frais;
    public float $montantTotal;
    public string $dateExpiration;

    public function __construct(
        string $idPaiement,
        string $statut,
        array $marchand,
        float $montant,
        float $frais,
        float $montantTotal,
        string $dateExpiration
    ) {
        $this->idPaiement = $idPaiement;
        $this->statut = $statut;
        $this->marchand = $marchand;
        $this->montant = $montant;
        $this->frais = $frais;
        $this->montantTotal = $montantTotal;
        $this->dateExpiration = $dateExpiration;
    }
}