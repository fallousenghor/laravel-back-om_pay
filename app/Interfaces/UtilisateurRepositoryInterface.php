<?php

namespace App\Interfaces;

use App\Models\Utilisateur;

interface UtilisateurRepositoryInterface
{
    public function findByPhoneNumber(string $numeroTelephone): ?Utilisateur;
    public function findByCni(string $numeroCni): ?Utilisateur;
    public function create(array $data): Utilisateur;
    public function update(Utilisateur $utilisateur, array $data): bool;
}