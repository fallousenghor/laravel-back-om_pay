<?php

namespace App\Interfaces;

interface PaiementServiceInterface
{
    public function listerCategories();
    public function scannerQR($donneesQR);
    public function saisirCode($code);
    public function initierPaiement($utilisateur, $data);
    public function confirmerPaiement($utilisateur, $idPaiement, $codePin);
    public function annulerPaiement($utilisateur, $idPaiement);
}