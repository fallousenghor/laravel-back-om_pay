<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortefeuilleController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\PaiementController;

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
    Route::prefix('{numeroCompte}/portefeuille')->group(function () {
        Route::get('solde', [PortefeuilleController::class, 'consulterSolde']); // Consulter solde du compte OM Pay
        Route::get('transactions', [PortefeuilleController::class, 'historiqueTransactions']); // Liste des transactions
        Route::get('transactions/{idTransaction}', [PortefeuilleController::class, 'detailsTransaction']); // Détail d’une transaction
    });

    /*
    |--------------------------------------------------------------------------
    | Transferts
    |--------------------------------------------------------------------------
    */
    Route::prefix('{numeroCompte}/transfert')->group(function () {
        Route::post('initier', [TransfertController::class, 'initierTransfert']); // Initier un transfert
        Route::post('{idTransfert}/confirmer', [TransfertController::class, 'confirmerTransfert']); // Confirmer un transfert
        Route::delete('{idTransfert}/annuler', [TransfertController::class, 'annulerTransfert']); // Annuler un transfert
    });

    /*
    |--------------------------------------------------------------------------
    | Paiement Marchand
    |--------------------------------------------------------------------------
    */
    Route::prefix('{numeroCompte}/paiement')->group(function () {
        Route::post('scanner-qr', [PaiementController::class, 'scannerQR']); // Scanner un QR code
        Route::post('saisir-code', [PaiementController::class, 'saisirCode']); // Saisir un code de paiement
        Route::post('saisir-numero-telephone', [PaiementController::class, 'saisirNumeroTelephone']); // Saisir un numéro de téléphone pour paiement
        Route::post('{idPaiement}/confirmer', [PaiementController::class, 'confirmerPaiement']); // Confirmer paiement
        Route::delete('{idPaiement}/annuler', [PaiementController::class, 'annulerPaiement']); // Annuler paiement
    });


});

// ✅ Vérifier utilisateur connecté
Route::middleware(['auth.token', 'rate.limit'])->get('/{numeroCompte}/user', function (Request $request, $numeroCompte) {
    // Vérifier que le numéro de compte correspond à l'utilisateur connecté
    $utilisateur = $request->user();
    if ($utilisateur->numero_telephone !== $numeroCompte) {
        return response()->json([
            'success' => false,
            'message' => 'Numéro de compte invalide'
        ], 403);
    }
    return response()->json($request->user());
});

// 👤 Profil utilisateur
Route::middleware(['auth.token', 'rate.limit'])->group(function () {
    Route::prefix('{numeroCompte}/profil')->group(function () {
        Route::get('/', [AuthController::class, 'consulterProfil']); // Consulter le profil
        Route::put('/', [AuthController::class, 'mettreAJourProfil']); // Mettre à jour le profil
        Route::post('changer-pin', [AuthController::class, 'changerPin']); // Changer le code PIN
        Route::post('activer-biometrie', [AuthController::class, 'activerBiometrie']); // Activer la biométrie
    });
});

// � Tableau de bord
Route::middleware(['auth.token', 'rate.limit'])->group(function () {
    Route::get('{numeroCompte}/dashboard', [AuthController::class, 'dashboard']); // Informations complètes de l'utilisateur
});
