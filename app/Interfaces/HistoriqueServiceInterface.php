<?php

namespace App\Interfaces;

interface HistoriqueServiceInterface
{
    public function rechercher($utilisateur, $filters);
}