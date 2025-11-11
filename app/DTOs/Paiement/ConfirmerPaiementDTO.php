<?php

namespace App\DTOs\Paiement;

class ConfirmerPaiementDTO
{
    public string $idTransaction;
    public string $statut;
    public array $marchand;
    public float $montant;
    public string $dateTransaction;
    public string $reference;
    public string $recu;

    public function __construct(
        string $idTransaction,
        string $statut,
        array $marchand,
        float $montant,
        string $dateTransaction,
        string $reference,
        string $recu
    ) {
        $this->idTransaction = $idTransaction;
        $this->statut = $statut;
        $this->marchand = $marchand;
        $this->montant = $montant;
        $this->dateTransaction = $dateTransaction;
        $this->reference = $reference;
        $this->recu = $recu;
    }
}