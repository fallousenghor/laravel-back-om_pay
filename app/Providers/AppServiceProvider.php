<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\PortefeuilleServiceInterface;
use App\Interfaces\TransfertServiceInterface;
use App\Interfaces\PaiementServiceInterface;
use App\Interfaces\ContactServiceInterface;
use App\Interfaces\HistoriqueServiceInterface;
use App\Services\PortefeuilleService;
use App\Services\TransfertService;
use App\Services\PaiementService;
use App\Services\ContactService;
use App\Services\HistoriqueService;
use App\Services\AuthService;
use App\Services\TokenService;
use App\Services\OtpService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PortefeuilleServiceInterface::class, PortefeuilleService::class);
        $this->app->bind(TransfertServiceInterface::class, TransfertService::class);
        $this->app->bind(PaiementServiceInterface::class, PaiementService::class);
        $this->app->bind(ContactServiceInterface::class, ContactService::class);
        $this->app->bind(HistoriqueServiceInterface::class, HistoriqueService::class);

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
        //
    }
}
