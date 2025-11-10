<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Utilisateur;
use App\Models\OrangeMoney;
use Illuminate\Support\Facades\Hash;

try {
    $numero_telephone = '+221771234567';
    
    // Vérifier si le numéro existe dans Orange Money
    $compte_om = OrangeMoney::where('numero_telephone', $numero_telephone)->first();
    
    if (!$compte_om) {
        throw new Exception("Ce numéro n'a pas de compte Orange Money");
    }
    
    // Simuler l'envoi d'un code OTP
    $code_otp = rand(100000, 999999);
    echo "Code OTP envoyé (simulation) : " . $code_otp . "\n";
    
    // Dans un cas réel, vous devriez vérifier le code OTP ici
    // Pour la simulation, on continue directement
    
    $u = Utilisateur::create([
        'numero_telephone' => $numero_telephone,
        'prenom' => $compte_om->prenom,
        'nom' => $compte_om->nom,
        'email' => 'tmp.user@example.com',
        'code_pin' => Hash::make('0000'),
        'numero_cni' => $compte_om->numero_cni,
        'statut_kyc' => 'verifie'
    ]);
    echo "Created: ".json_encode($u->toArray())."\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
