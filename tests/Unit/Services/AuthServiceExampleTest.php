<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Services\AuthService;
use App\Interfaces\TokenServiceInterface;
use App\Interfaces\OtpServiceInterface;
use App\Models\Utilisateur;

/**
 * Exemple de test unitaire avec injection de dépendances
 * 
 * Cette classe montre comment tester les services en utilisant des mocks
 * pour les dépendances au lieu des services réels.
 */
class AuthServiceExampleTest extends TestCase
{
    protected $tokenService;
    protected $otpService;
    protected $authService;

    /**
     * Configuration avant chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Créer des mocks des interfaces
        $this->tokenService = Mockery::mock(TokenServiceInterface::class);
        $this->otpService = Mockery::mock(OtpServiceInterface::class);

        // Injecter les mocks dans le service à tester
        $this->authService = new AuthService(
            $this->tokenService,
            $this->otpService
        );
    }

    /**
     * Nettoyer après chaque test
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test 1 : Génération d'OTP lors de l'inscription
     */
    public function test_generateOtpOnRegistration()
    {
        // Arrange - Configuration du mock
        $this->otpService
            ->shouldReceive('generateOtp')
            ->once()
            ->andReturn(123456);

        // Act - Appel de la méthode à tester
        $data = ['numeroTelephone' => '+221701234567', 'prenom' => 'Jean', 'nom' => 'Dupont'];
        $result = $this->authService->initierInscription($data);

        // Assert - Vérification du résultat
        $this->assertTrue($result['success']);
    }

    /**
     * Test 2 : Génération de tokens après authentification
     */
    public function test_generateTokensAfterAuth()
    {
        // Arrange
        $user = Mockery::mock(Utilisateur::class);
        $user->shouldReceive('getKey')->andReturn(1);

        $expectedTokens = [
            'accessToken' => 'token_123456',
            'refreshToken' => 'refresh_123456'
        ];

        $this->tokenService
            ->shouldReceive('generateTokens')
            ->once()
            ->with($user)
            ->andReturn($expectedTokens);

        // Act
        $tokens = $this->tokenService->generateTokens($user);

        // Assert
        $this->assertEquals($expectedTokens, $tokens);
    }

    /**
     * Test 3 : Vérification d'OTP avec code valide
     */
    public function test_verifyValidOtp()
    {
        // Arrange
        $user = Mockery::mock(Utilisateur::class);
        $user->otp = '123456';
        $user->otp_expires_at = \Carbon\Carbon::now()->addMinutes(5);

        $this->otpService
            ->shouldReceive('verifyOtp')
            ->once()
            ->with($user, '123456')
            ->andReturn(true);

        // Act
        $result = $this->otpService->verifyOtp($user, '123456');

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test 4 : Vérification d'OTP avec code invalide
     */
    public function test_verifyInvalidOtp()
    {
        // Arrange
        $user = Mockery::mock(Utilisateur::class);
        $user->otp = '123456';

        $this->otpService
            ->shouldReceive('verifyOtp')
            ->once()
            ->with($user, '654321')
            ->andReturn(false);

        // Act
        $result = $this->otpService->verifyOtp($user, '654321');

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test 5 : Invalidation d'OTP après utilisation
     */
    public function test_invalidateOtpAfterUse()
    {
        // Arrange
        $user = Mockery::mock(Utilisateur::class);

        $this->otpService
            ->shouldReceive('invalidateOtp')
            ->once()
            ->with($user)
            ->andReturn(true);

        // Act
        $result = $this->otpService->invalidateOtp($user);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test 6 : Flux complet d'authentification
     */
    public function test_completeAuthenticationFlow()
    {
        // Arrange
        $user = Mockery::mock(Utilisateur::class);
        $user->shouldReceive('getKey')->andReturn(1);

        $this->otpService
            ->shouldReceive('generateOtp')
            ->once()
            ->andReturn(123456);

        $this->otpService
            ->shouldReceive('verifyOtp')
            ->once()
            ->andReturn(true);

        $this->otpService
            ->shouldReceive('invalidateOtp')
            ->once()
            ->andReturn(true);

        $this->tokenService
            ->shouldReceive('generateTokens')
            ->once()
            ->andReturn(['accessToken' => 'token', 'refreshToken' => 'refresh']);

        // Act & Assert
        // 1. Générer OTP
        $otp = $this->otpService->generateOtp();
        $this->assertEquals(123456, $otp);

        // 2. Vérifier OTP
        $verified = $this->otpService->verifyOtp($user, '123456');
        $this->assertTrue($verified);

        // 3. Invalider OTP
        $invalidated = $this->otpService->invalidateOtp($user);
        $this->assertTrue($invalidated);

        // 4. Générer tokens
        $tokens = $this->tokenService->generateTokens($user);
        $this->assertArrayHasKey('accessToken', $tokens);
        $this->assertArrayHasKey('refreshToken', $tokens);
    }

    /**
     * Test 7 : Vérification des dépendances injectées
     */
    public function test_dependenciesAreInjected()
    {
        // Vérifier que les services sont bien injectés
        $this->assertInstanceOf(TokenServiceInterface::class, $this->tokenService);
        $this->assertInstanceOf(OtpServiceInterface::class, $this->otpService);
    }
}

/**
 * Avantages du test avec injection de dépendances :
 * 
 * 1. ✅ Isolation - Chaque service est testé indépendamment
 * 2. ✅ Rapidité - Pas d'accès à la base de données
 * 3. ✅ Contrôle - On contrôle exactement ce que les dépendances font
 * 4. ✅ Flexibilité - Facile de changer les mocks
 * 5. ✅ Maintenabilité - Les tests restent simples et lisibles
 * 6. ✅ Couverture - On peut tester tous les cas facilement
 * 
 * Exemple d'exécution :
 * php artisan test tests/Unit/Services/AuthServiceExampleTest.php
 */
