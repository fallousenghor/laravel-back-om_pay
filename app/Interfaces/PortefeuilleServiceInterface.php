<?php

namespace App\Interfaces;

interface PortefeuilleServiceInterface
{
    public function consulterSolde($utilisateur);
    public function historiqueTransactions($utilisateur, $filters, $page, $limite);
    public function detailsTransaction($utilisateur, $idTransaction);
}