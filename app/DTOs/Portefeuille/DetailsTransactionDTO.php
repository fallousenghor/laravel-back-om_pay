<?php

namespace App\DTOs\Portefeuille;

class DetailsTransactionDTO
{
    public string $idTransaction;
    public string $type;
    public float $montant;
    public string $devise;
    public ?array $expediteur;
    public ?array $destinataire;
    public string $statut;
    public string $dateTransaction;
    public string $reference;
    public ?float $frais;
    public ?string $note;

    public function __construct(
        string $idTransaction,
        string $type,
        float $montant,
        string $devise,
        ?array $expediteur,
        ?array $destinataire,
        string $statut,
        string $dateTransaction,
        string $reference,
        ?float $frais,
        ?string $note
    ) {
        $this->idTransaction = $idTransaction;
        $this->type = $type;
        $this->montant = $montant;
        $this->devise = $devise;
        $this->expediteur = $expediteur;
        $this->destinataire = $destinataire;
        $this->statut = $statut;
        $this->dateTransaction = $dateTransaction;
        $this->reference = $reference;
        $this->frais = $frais;
        $this->note = $note;
    }
}