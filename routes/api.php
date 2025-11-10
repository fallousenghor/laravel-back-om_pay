<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortefeuilleController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HistoriqueController;

/*
|--------------------------------------------------------------------------
| API Routes - OM Pay
|--------------------------------------------------------------------------
| Ces routes définissent toutes les interactions de l'application OM Pay :
| - Authentification & création de compte
| - Gestion du portefeuille et des transactions
| - Transfert d'argent
| - Paiement marchand
| - Historique
|--------------------------------------------------------------------------
*/

// 🔐 Authentification et création de compte
Route::prefix('auth')->group(function () {
    Route::post('initiate', [AuthController::class, 'initiateRegistration']); // Saisie du numéro et envoi OTP
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']); // Vérification du code OTP
    Route::post('create-account', [AuthController::class, 'createAccount']); // Création du compte OM Pay
    Route::post('login', [AuthController::class, 'login']); // Connexion avec PIN
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth.token'); // Déconnexion
});

// 🧍‍♂️ Routes protégées (nécessitent un token valide)
Route::middleware(['auth.token', 'rate.limit'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Portefeuille
    |--------------------------------------------------------------------------
    */
    Route::prefix('portefeuille')->group(function () {
        Route::get('solde', [PortefeuilleController::class, 'consulterSolde']); // Consulter solde du compte OM Pay
        Route::get('transactions', [PortefeuilleController::class, 'historiqueTransactions']); // Liste des transactions
        Route::get('transactions/{idTransaction}', [PortefeuilleController::class, 'detailsTransaction']); // Détail d’une transaction
    });

    /*
    |--------------------------------------------------------------------------
    | Transferts
    |--------------------------------------------------------------------------
    */
    Route::prefix('transfert')->group(function () {
        Route::post('verifier-destinataire', [TransfertController::class, 'verifierDestinataire']); // Vérification destinataire avant transfert
        Route::post('initier', [TransfertController::class, 'initierTransfert']); // Initier un transfert
        Route::post('{idTransfert}/confirmer', [TransfertController::class, 'confirmerTransfert']); // Confirmer un transfert
        Route::delete('{idTransfert}/annuler', [TransfertController::class, 'annulerTransfert']); // Annuler un transfert
    });

    /*
    |--------------------------------------------------------------------------
    | Paiement Marchand
    |--------------------------------------------------------------------------
    */
    Route::prefix('paiement')->group(function () {
        Route::post('verifier-marchand', [PaiementController::class, 'verifierMarchand']); // Vérifier code marchand
        Route::post('initier', [PaiementController::class, 'initierPaiement']); // Initier paiement marchand
        Route::post('{idPaiement}/confirmer', [PaiementController::class, 'confirmerPaiement']); // Confirmer paiement
    });

    /*
    |--------------------------------------------------------------------------
    | Historique
    |--------------------------------------------------------------------------
    */
    Route::prefix('historique')->group(function () {
        Route::get('rechercher', [HistoriqueController::class, 'rechercher']); // Recherche dans l’historique
    });

    /*
    |--------------------------------------------------------------------------
    | Contacts (optionnel : pour envoyer de l’argent facilement)
    |--------------------------------------------------------------------------
    */
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'liste']); // Liste des contacts enregistrés
        Route::post('ajouter', [ContactController::class, 'ajouter']); // Ajouter un contact
        Route::delete('{id}', [ContactController::class, 'supprimer']); // Supprimer un contact
    });
});

// ✅ Vérifier utilisateur connecté
Route::middleware('auth.token')->get('/user', function (Request $request) {
    return response()->json($request->user());
});
