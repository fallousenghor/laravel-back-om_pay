<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Utilisateur;
use App\Interfaces\ContactServiceInterface;
use Illuminate\Support\Str;

class ContactService implements ContactServiceInterface
{
    // 5.1 Lister les Contacts
    public function listerContacts($utilisateur, $filters, $page, $limite)
    {
        $query = Contact::where('idUtilisateur', $utilisateur->idUtilisateur)
                        ->with('destinataire:idUtilisateur,numeroTelephone,prenom,nom');

        if (isset($filters['recherche'])) {
            $recherche = $filters['recherche'];
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                  ->orWhereHas('destinataire', function ($subQ) use ($recherche) {
                      $subQ->where('numeroTelephone', 'like', "%{$recherche}%");
                  });
            });
        }

        $contacts = $query->paginate($limite, ['*'], 'page', $page);

        $data = $contacts->map(function ($contact) {
            $destinataire = $contact->destinataire;
            $nombreTransactions = 0; // Calculer depuis les transactions
            $derniereTransaction = null; // Récupérer la dernière transaction

            return [
                'idContact' => $contact->idContact,
                'nom' => $contact->nom,
                'numeroTelephone' => $destinataire ? $destinataire->numeroTelephone : null,
                'photo' => $contact->photo,
                'nombreTransactions' => $nombreTransactions,
                'derniereTransaction' => $derniereTransaction,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'contacts' => $data,
                'pagination' => [
                    'pageActuelle' => $contacts->currentPage(),
                    'totalPages' => $contacts->lastPage(),
                    'totalElements' => $contacts->total(),
                ]
            ]
        ];
    }

    // 5.2 Ajouter un Contact
    public function ajouterContact($utilisateur, $data)
    {
        // Vérifier si le destinataire existe
        $destinataire = Utilisateur::where('numeroTelephone', $data['numeroTelephone'])->first();
        if (!$destinataire) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'CONTACT_003',
                    'message' => 'Numéro invalide'
                ],
                'status' => 422
            ];
        }

        // Vérifier si le contact existe déjà
        $contactExistant = Contact::where('idUtilisateur', $utilisateur->idUtilisateur)
                                  ->where('idDestinataire', $destinataire->idUtilisateur)
                                  ->first();

        if ($contactExistant) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'CONTACT_002',
                    'message' => 'Contact déjà existant'
                ],
                'status' => 409
            ];
        }

        $idContact = 'cnt_' . Str::random(10);

        $contact = Contact::create([
            'idContact' => $idContact,
            'idUtilisateur' => $utilisateur->idUtilisateur,
            'idDestinataire' => $destinataire->idUtilisateur,
            'nom' => $data['nom'],
            'photo' => $data['photo'] ?? null,
        ]);

        return [
            'success' => true,
            'data' => [
                'idContact' => $contact->idContact,
                'nom' => $contact->nom,
                'numeroTelephone' => $destinataire->numeroTelephone,
                'photo' => $contact->photo,
                'nombreTransactions' => 0,
            ],
            'message' => 'Contact ajouté avec succès',
            'status' => 201
        ];
    }
}