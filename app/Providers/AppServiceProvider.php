<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Interfaces\PortefeuilleServiceInterface;
use App\Interfaces\TransfertServiceInterface;
use App\Interfaces\PaiementServiceInterface;
use App\Interfaces\AuthenticationServiceInterface;
use App\Interfaces\UtilisateurRepositoryInterface;
use App\Interfaces\OrangeMoneyRepositoryInterface;
use App\Interfaces\VerificationCodeRepositoryInterface;
use App\Interfaces\SessionOmpayRepositoryInterface;
use App\Interfaces\QRCodeRepositoryInterface;
use App\Services\PortefeuilleService;
use App\Services\TransfertService;
use App\Services\PaiementService;
use App\Services\AuthenticationService;
use App\Services\AuthService;
use App\Services\TokenService;
use App\Services\OtpService;
use App\Repositories\UtilisateurRepository;
use App\Repositories\OrangeMoneyRepository;
use App\Repositories\VerificationCodeRepository;
use App\Repositories\SessionOmpayRepository;
use App\Repositories\QRCodeRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UtilisateurRepositoryInterface::class, UtilisateurRepository::class);
        $this->app->bind(OrangeMoneyRepositoryInterface::class, OrangeMoneyRepository::class);
        $this->app->bind(VerificationCodeRepositoryInterface::class, VerificationCodeRepository::class);
        $this->app->bind(SessionOmpayRepositoryInterface::class, SessionOmpayRepository::class);
        $this->app->bind(QRCodeRepositoryInterface::class, QRCodeRepository::class);

        // Service bindings
        $this->app->bind(PortefeuilleServiceInterface::class, PortefeuilleService::class);
        $this->app->bind(TransfertServiceInterface::class, TransfertService::class);
        $this->app->bind(PaiementServiceInterface::class, PaiementService::class);
        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);

        // Auth services
        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(TokenService::class),
                $app->make(OtpService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme for generated URLs when running in production
        // or when FORCE_HTTPS env var is set. This prevents mixed-content
        // issues for assets (e.g. swagger-ui.css) when the site is served
        // over HTTPS but APP_URL or URL generation defaults to http.
        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
