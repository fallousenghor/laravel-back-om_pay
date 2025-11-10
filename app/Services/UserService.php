<?php

namespace App\Services;

use App\Models\Utilisateur;

class UserService
{
    // Consulter le profil
    public function consulterProfil($utilisateur)
    {
        return [
            'success' => true,
            'data' => [
                'idUtilisateur' => $utilisateur->getKey(),
                'numeroTelephone' => $utilisateur->numero_telephone,
                'prenom' => $utilisateur->prenom,
                'nom' => $utilisateur->nom,
                'email' => $utilisateur->email,
                'numeroCNI' => $utilisateur->numero_cni ?? null,
                'statutKYC' => $utilisateur->statut_kyc ?? null,
                'biometrieActivee' => $utilisateur->biometrie_activee ?? false,
                'dateCreation' => optional($utilisateur->date_creation)?->toIso8601String(),
                'derniereConnexion' => optional($utilisateur->derniere_connexion)?->toIso8601String(),
            ]
        ];
    }

    // Mettre à jour le profil
    public function mettreAJourProfil($utilisateur, $data)
    {
        $utilisateur->update($data);

        return [
            'success' => true,
            'data' => [
                'idUtilisateur' => $utilisateur->getKey(),
                'prenom' => $utilisateur->prenom,
                'nom' => $utilisateur->nom,
                'email' => $utilisateur->email,
            ],
            'message' => 'Profil mis à jour avec succès'
        ];
    }
}