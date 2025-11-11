<?php

namespace App\DTOs\Paiement;

class AnnulerPaiementDTO
{
    public string $idPaiement;
    public string $statut;
    public string $dateAnnulation;

    public function __construct(string $idPaiement, string $statut, string $dateAnnulation)
    {
        $this->idPaiement = $idPaiement;
        $this->statut = $statut;
        $this->dateAnnulation = $dateAnnulation;
    }
}