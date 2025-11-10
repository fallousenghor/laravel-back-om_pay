<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;

class ApiEndpointsTest extends TestCase
{
    /** @test */
    public function full_auth_flow_and_protected_endpoint()
    {
        // Générer un numéro unique au format +221XXXXXXXXX
        $numero = '+221' . mt_rand(100000000, 999999999);

        // 1) Inscription
        $inscriptionPayload = [
            'numeroTelephone' => $numero,
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test+' . mt_rand(1000,9999) . '@example.com',
            'codePin' => '1234',
            'numeroCNI' => (string) mt_rand(100000000000, 999999999999),
        ];

        $this->postJson('/api/auth/inscription', $inscriptionPayload)
              ->assertStatus(201)
              ->assertJsonPath('success', true);

        // 2) Récupérer l'OTP directement depuis la base
        $user = Utilisateur::where('numero_telephone', $numero)->first();
        $this->assertNotNull($user, 'Utilisateur non créé en base après inscription');
        $this->assertNotNull($user->otp, 'OTP non enregistré en base');

        // 3) Vérification OTP
        $verificationPayload = [
            'numeroTelephone' => $numero,
            'codeOTP' => $user->otp,
        ];

        $response = $this->postJson('/api/auth/verification-otp', $verificationPayload)
                          ->assertStatus(200)
                          ->assertJsonPath('success', true)
                          ->assertJsonStructure(['data' => ['jetonAcces', 'jetonRafraichissement', 'utilisateur']]);

        $accessToken = $response->json('data.jetonAcces');
        $this->assertNotEmpty($accessToken, 'jetonAcces manquant');

        // 4) Appeler un endpoint protégé: consulter profil
        $this->withHeaders(['Authorization' => 'Bearer ' . $accessToken])
              ->getJson('/api/utilisateurs/profil')
              ->assertStatus(200)
              ->assertJsonPath('success', true)
              ->assertJsonStructure(['data' => ['idUtilisateur', 'numeroTelephone', 'prenom', 'nom']]);

        // 5) Tester consulter solde
        $this->withHeaders(['Authorization' => 'Bearer ' . $accessToken])
              ->getJson('/api/portefeuille/solde')
              ->assertStatus(200)
              ->assertJsonPath('success', true)
              ->assertJsonStructure(['data' => ['idPortefeuille', 'solde', 'soldeDisponible', 'soldeEnAttente', 'devise', 'derniereMiseAJour']]);

        // 6) Tester historique des transactions
        $this->withHeaders(['Authorization' => 'Bearer ' . $accessToken])
              ->getJson('/api/portefeuille/transactions')
              ->assertStatus(200)
              ->assertJsonPath('success', true)
              ->assertJsonStructure(['data' => ['transactions', 'pagination']]);
    }
}
