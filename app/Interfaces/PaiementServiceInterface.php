<?php

namespace App\Interfaces;

interface PaiementServiceInterface
{
    public function listerCategories();
    public function scannerQR($donneesQR);
    public function saisirCode($utilisateur, $code, $montant);
    public function initierPaiement($utilisateur, $data);
    public function confirmerPaiement($utilisateur, $idPaiement, $codePin, $montant = null);
    public function annulerPaiement($utilisateur, $idPaiement);
}