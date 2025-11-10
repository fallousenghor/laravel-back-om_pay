<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AuthenticationService;

try {
    $authService = new AuthenticationService();
    
    // Test avec un numéro existant
    $numero_telephone = '+221771234567';
    
    // Étape 1 : Initier la connexion
    echo "Étape 1 : Initiation de la connexion\n";
    $result = $authService->initiateLogin($numero_telephone);
    echo "SMS simulé envoyé avec:\n";
    echo "Code: " . $result['code'] . "\n";
    echo "Lien: " . $result['lien'] . "\n\n";

    // Étape 2 : Vérifier le code
    echo "Étape 2 : Vérification du code\n";
    $token = explode('/', $result['lien'])[4]; // Extraire le token du lien
    $verifyResult = $authService->verifyCode($token, $result['code']);
    
    if ($verifyResult['status'] === 'first_access') {
        // Étape 3 : Créer le compte avec un code PIN
        echo "Étape 3 : Création du compte avec code PIN\n";
        $code_pin = '1234'; // Code PIN de test
        $finalResult = $authService->createAccount($numero_telephone, $code_pin, $token);
        echo "Compte créé avec succès!\n";
        echo "Session token: " . $finalResult['session_token'] . "\n";
        echo "Utilisateur: " . json_encode($finalResult['user']) . "\n";
    } else {
        echo "L'utilisateur existe déjà\n";
        echo "Session token: " . $verifyResult['session_token'] . "\n";
        echo "Utilisateur: " . json_encode($verifyResult['user']) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}