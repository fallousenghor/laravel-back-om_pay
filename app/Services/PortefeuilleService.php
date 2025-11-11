<?php

namespace App\Services;

use App\DTOs\Portefeuille\SoldeDTO;
use App\DTOs\Portefeuille\HistoriqueTransactionsDTO;
use App\DTOs\Portefeuille\DetailsTransactionDTO;
use App\Interfaces\PortefeuilleServiceInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Traits\PortefeuilleResponseTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PortefeuilleService implements PortefeuilleServiceInterface
{
    use PortefeuilleResponseTrait;

    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    // 2.1 Consulter le Solde
    public function consulterSolde($utilisateur)
    {
        // Business logic: Vérifier l'existence du portefeuille
        $portefeuille = $utilisateur->portefeuille;

        if (!$portefeuille) {
            return $this->portefeuilleErrorResponse('Portefeuille introuvable', 404);
        }

        // Business logic: Retourner le solde
        $dto = new SoldeDTO($portefeuille->solde);
        return $this->soldeResponse($dto);
    }

    // 2.2 Historique des Transactions
    public function historiqueTransactions($utilisateur, $filters, $page, $limite)
    {
        // Business logic: Récupérer les transactions paginées
        $paginator = $this->transactionRepository->findByUserId(
            $utilisateur->id,
            $filters,
            $page,
            $limite
        );

        // Business logic: Transformer les données pour inclure destinataire/marchand
        $paginator = $this->enrichTransactionsWithRelations($paginator);

        // Business logic: Créer le DTO et retourner la réponse
        $dto = HistoriqueTransactionsDTO::fromPaginator($paginator);
        return $this->historiqueTransactionsResponse($dto);
    }

    private function enrichTransactionsWithRelations(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->getCollection()->transform(function ($transaction) {
            $destinataire = null;
            $marchand = null;

            if ($transaction->type === 'transfert') {
                $transfert = $transaction->transfert;
                if ($transfert) {
                    $destinataire = [
                        'numeroTelephone' => $transfert->numero_telephone_destinataire,
                        'nom' => $transfert->nom_destinataire,
                    ];
                }
            } elseif ($transaction->type === 'paiement') {
                $paiement = $transaction->paiement;
                if ($paiement && $paiement->marchand) {
                    $marchand = [
                        'nom' => $paiement->marchand->nom,
                        'categorie' => $paiement->marchand->categorie ?? 'General',
                    ];
                }
            }

            $transaction->destinataire = $destinataire;
            $transaction->marchand = $marchand;

            return $transaction;
        });

        return $paginator;
    }

    // 2.3 Détails d'une Transaction
    public function detailsTransaction($utilisateur, $idTransaction)
    {
        // Business logic: Trouver la transaction
        $transaction = $this->transactionRepository->findById($idTransaction);

        if (!$transaction || $transaction->id_utilisateur !== $utilisateur->id) {
            return $this->portefeuilleErrorResponse('Transaction introuvable', 404);
        }

        // Business logic: Construire les informations d'expéditeur/destinataire
        $expediteur = null;
        $destinataire = null;
        $note = null;

        if ($transaction->type === 'transfert') {
            $expediteur = [
                'numeroTelephone' => $utilisateur->numero_telephone,
                'nom' => $utilisateur->prenom . ' ' . $utilisateur->nom,
            ];
            $transfert = $transaction->transfert;
            if ($transfert) {
                $destinataire = [
                    'numeroTelephone' => $transfert->numero_telephone_destinataire,
                    'nom' => $transfert->nom_destinataire,
                ];
                $note = $transfert->note;
            }
        }

        // Business logic: Créer le DTO et retourner la réponse
        $dto = new DetailsTransactionDTO(
            $transaction->id,
            $transaction->type,
            $transaction->montant,
            $transaction->devise,
            $expediteur,
            $destinataire,
            $transaction->statut,
            $transaction->date_transaction->toISOString(),
            $transaction->reference,
            $transaction->frais,
            $note
        );

        return $this->detailsTransactionResponse($dto);
    }
}