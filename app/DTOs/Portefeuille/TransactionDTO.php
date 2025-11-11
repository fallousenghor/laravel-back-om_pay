<?php

namespace App\DTOs\Portefeuille;

class TransactionDTO
{
    public string $idTransaction;
    public string $type;
    public float $montant;
    public string $devise;
    public ?array $destinataire;
    public ?array $marchand;
    public string $statut;
    public string $dateTransaction;
    public string $reference;
    public ?float $frais;

    public function __construct(
        string $idTransaction,
        string $type,
        float $montant,
        string $devise,
        ?array $destinataire,
        ?array $marchand,
        string $statut,
        string $dateTransaction,
        string $reference,
        ?float $frais
    ) {
        $this->idTransaction = $idTransaction;
        $this->type = $type;
        $this->montant = $montant;
        $this->devise = $devise;
        $this->destinataire = $destinataire;
        $this->marchand = $marchand;
        $this->statut = $statut;
        $this->dateTransaction = $dateTransaction;
        $this->reference = $reference;
        $this->frais = $frais;
    }
}