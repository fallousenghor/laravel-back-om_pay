<?php

namespace App\Services;

use App\DTOs\Paiement\CategoriesDTO;
use App\DTOs\Paiement\ScanQRDTO;
use App\DTOs\Paiement\SaisirCodeDTO;
use App\DTOs\Paiement\InitierPaiementDTO;
use App\DTOs\Paiement\ConfirmerPaiementDTO;
use App\DTOs\Paiement\AnnulerPaiementDTO;
use App\Interfaces\PaiementServiceInterface;
use App\Interfaces\MarchandRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\PaiementRepositoryInterface;
use App\Interfaces\PortefeuilleRepositoryInterface;
use App\Interfaces\UtilisateurRepositoryInterface;
use App\Traits\PaiementResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaiementService implements PaiementServiceInterface
{
    use PaiementResponseTrait;

    private MarchandRepositoryInterface $marchandRepository;
    private TransactionRepositoryInterface $transactionRepository;
    private PaiementRepositoryInterface $paiementRepository;
    private PortefeuilleRepositoryInterface $portefeuilleRepository;
    private UtilisateurRepositoryInterface $utilisateurRepository;

    public function __construct(
        MarchandRepositoryInterface $marchandRepository,
        TransactionRepositoryInterface $transactionRepository,
        PaiementRepositoryInterface $paiementRepository,
        PortefeuilleRepositoryInterface $portefeuilleRepository,
        UtilisateurRepositoryInterface $utilisateurRepository
    ) {
        $this->marchandRepository = $marchandRepository;
        $this->transactionRepository = $transactionRepository;
        $this->paiementRepository = $paiementRepository;
        $this->portefeuilleRepository = $portefeuilleRepository;
        $this->utilisateurRepository = $utilisateurRepository;
    }
    // 4.1 Lister les Catégories de Marchands
    public function listerCategories()
    {
        // Business logic: Récupérer tous les marchands et les grouper par catégorie
        $marchands = $this->marchandRepository->getAllGroupedByCategory();

        $categories = $marchands->map(function ($items, $categorie) {
            return [
                'idCategorie' => 'cat_' . strtolower(str_replace(' ', '_', $categorie)),
                'nom' => $categorie,
                'description' => 'Description de ' . $categorie,
                'icone' => strtolower(str_replace(' ', '_', $categorie)),
                'nombreMarchands' => count($items),
            ];
        })->values();

        // Business logic: Créer le DTO et retourner la réponse
        $dto = new CategoriesDTO($categories->toArray());
        return $this->categoriesResponse($dto);
    }

    // 4.2 Scanner un QR Code
    public function scannerQR($donneesQR)
    {
        // Business logic: Parser les données QR
        $parts = explode('_', $donneesQR);
        if (count($parts) !== 6 || $parts[0] !== 'OM' || $parts[1] !== 'PAY') {
            return $this->paiementErrorResponse('QR Code invalide', 422);
        }

        $idMarchand = $parts[2];
        $montant = (int) $parts[3];
        $timestamp = $parts[4];

        // Business logic: Vérifier l'existence du marchand
        $marchand = $this->marchandRepository->findById($idMarchand);
        if (!$marchand) {
            return $this->paiementErrorResponse('Marchand introuvable', 404);
        }

        // Business logic: Vérifier l'expiration du QR
        $qrTime = Carbon::createFromTimestamp($timestamp);
        if (Carbon::now()->diffInMinutes($qrTime) > 5) {
            return $this->paiementErrorResponse('QR Code expiré', 422);
        }

        // Business logic: Générer l'ID de scan et créer le DTO
        $idScan = 'scn_' . Str::random(10);

        $dto = new ScanQRDTO(
            $idScan,
            [
                'idMarchand' => $marchand->id,
                'nom' => $marchand->nom,
                'logo' => $marchand->logo,
            ],
            $montant,
            'XOF',
            $qrTime->addMinutes(5)->toIso8601String(),
            true
        );

        return $this->scanQRResponse($dto);
    }

    // 4.3 Saisir un Code de Paiement
    public function saisirCode($code)
    {
        // Business logic: Trouver le compte OrangeMoney actif avec ce code
        $compte = $this->orangeMoneyRepository->findByCode($code);
        if (!$compte) {
            return $this->paiementErrorResponse('Code de paiement invalide', 422);
        }

        // Business logic: Générer l'ID de code et créer le DTO
        $idCode = 'cod_' . Str::random(10);

        $dto = new SaisirCodeDTO(
            $idCode,
            [
                'idMarchand' => $compte->id,
                'nom' => ($compte->prenom ?? '') . ' ' . ($compte->nom ?? ''),
                'logo' => null,
            ],
            null, // Pas de montant fixe
            'XOF',
            null, // Pas de date d'expiration
            true
        );

        return $this->saisirCodeResponse($dto);
    }

    // 4.4 Initier un Paiement
    public function initierPaiement($utilisateur, $data)
    {
        // Business logic: Vérifier l'existence du marchand
        $marchand = $this->marchandRepository->findById($data['idMarchand']);
        if (!$marchand) {
            return $this->paiementErrorResponse('Marchand introuvable', 404);
        }

        // Business logic: Vérifier le solde du portefeuille
        $portefeuille = $this->portefeuilleRepository->findOrCreateByUserId($utilisateur->id);
        if ($portefeuille->solde < $data['montant']) {
            return $this->paiementErrorResponse('Solde insuffisant', 422);
        }

        // Business logic: Créer la transaction et le paiement
        $transaction = $this->transactionRepository->create([
            'id_utilisateur' => $utilisateur->id,
            'type' => 'paiement',
            'montant' => $data['montant'],
            'devise' => 'XOF',
            'nom_marchand' => $marchand->nom,
            'categorie_marchand' => $marchand->categorie,
        ]);

        $this->paiementRepository->create([
            'id' => $transaction->id,
            'id_marchand' => $data['idMarchand'],
            'mode_paiement' => $data['modePaiement'] ?? 'qr_code',
            'details_paiement' => $data['detailsPaiement'] ?? null,
        ]);

        // Business logic: Créer le DTO et retourner la réponse
        $dto = new InitierPaiementDTO(
            $transaction->id,
            $transaction->statut,
            [
                'idMarchand' => $marchand->id,
                'nom' => $marchand->nom,
                'logo' => $marchand->logo,
            ],
            $transaction->montant,
            0, // Frais à la charge du marchand
            $transaction->montant,
            $transaction->created_at->addMinutes(5)->toIso8601String()
        );

        return $this->initierPaiementResponse($dto);
    }

    // 4.5 Confirmer un Paiement
    public function confirmerPaiement($utilisateur, $idPaiement, $codePin)
    {
        // Business logic: Trouver la transaction en attente
        $transaction = $this->transactionRepository->findPendingByReferenceOrId($idPaiement, $utilisateur->id);
        if (!$transaction) {
            return $this->paiementErrorResponse('Paiement introuvable ou déjà confirmé', 404);
        }

        // Business logic: Valider le PIN
        if (!Hash::check($codePin, $utilisateur->code_pin)) {
            return $this->paiementErrorResponse('Code PIN incorrect', 401);
        }

        // Business logic: Vérifier le solde
        $portefeuille = $this->portefeuilleRepository->findOrCreateByUserId($utilisateur->id);
        if ($portefeuille->solde < $transaction->montant) {
            return $this->paiementErrorResponse('Solde insuffisant', 422);
        }

        // Business logic: Exécuter la transaction dans une transaction DB
        DB::transaction(function () use ($transaction, $portefeuille) {
            // Transition states: en_attente -> en_cours -> termine
            if (!$transaction->valider()) {
                throw new \Exception('Cannot transition to en_cours state');
            }

            // Debit user wallet
            $portefeuille->decrement('solde', $transaction->montant);

            // Execute payment
            if (!$transaction->executer()) {
                throw new \Exception('Cannot execute payment');
            }
        });

        // Business logic: Créer le DTO et retourner la réponse
        $dto = new ConfirmerPaiementDTO(
            $transaction->id,
            'termine',
            [
                'nom' => $transaction->nom_marchand,
                'categorie' => $transaction->categorie_marchand,
            ],
            $transaction->montant,
            $transaction->date_transaction->toIso8601String(),
            $transaction->reference,
            'https://cdn.ompay.sn/recus/' . $transaction->id . '.pdf'
        );

        return $this->confirmerPaiementResponse($dto);
    }

    // 4.6 Annuler un Paiement
    public function annulerPaiement($utilisateur, $idPaiement)
    {
        // Business logic: Trouver la transaction en attente
        $transaction = $this->transactionRepository->findPendingByReferenceOrId($idPaiement, $utilisateur->id);
        if (!$transaction) {
            return $this->paiementErrorResponse('Paiement introuvable ou déjà annulé', 404);
        }

        // Business logic: Annuler la transaction
        if (!$transaction->annuler()) {
            return $this->paiementErrorResponse('Impossible d\'annuler ce paiement', 422);
        }

        // Business logic: Créer le DTO et retourner la réponse
        $dto = new AnnulerPaiementDTO(
            $transaction->id,
            'ANNULE',
            $transaction->updated_at->toIso8601String()
        );

        return $this->annulerPaiementResponse($dto);
    }
}