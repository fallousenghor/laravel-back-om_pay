<?php

namespace App\Repositories;

use App\Models\Utilisateur;
use App\Interfaces\UtilisateurRepositoryInterface;

class UtilisateurRepository implements UtilisateurRepositoryInterface
{
    public function findByPhoneNumber(string $numeroTelephone): ?Utilisateur
    {
        return Utilisateur::where('numero_telephone', $numeroTelephone)->first();
    }

    public function findByCni(string $numeroCni): ?Utilisateur
    {
        return Utilisateur::where('numero_cni', $numeroCni)->first();
    }

    public function create(array $data): Utilisateur
    {
        return Utilisateur::create($data);
    }

    public function update(Utilisateur $utilisateur, array $data): bool
    {
        return $utilisateur->update($data);
    }
}