<?php

namespace App\Providers;

use App\Models\Lead;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use MercadoPago\SDK;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Contador de prospectos sin contestar para el menú de la cuenta.
        // Se consulta solo con sesión iniciada y se protege por si la tabla
        // aún no existe (deploy donde todavía no corrió la migración).
        View::composer('layouts.layout', function ($view) {
            $pendientes = 0;

            if (Auth::check() && Schema::hasTable('leads')) {
                $pendientes = Lead::visibleTo(Auth::user())->pending()->count();
            }

            $view->with('prospectosPendientes', $pendientes);
        });

        // Solo configuramos el SDK si el token está presente y parece real.
        // Llamar SDK::setAccessToken con un valor stub o vacío hace que el
        // SDK intente validar contra el endpoint de MP, lo cual:
        //  - rompe `artisan package:discover` en GitHub Actions (PolicyAgent
        //    bloquea ese egress), abortando el deploy antes de tocar FTP.
        //  - mete latencia innecesaria en cualquier artisan command corrido
        //    sin credenciales (tests, CI, migraciones locales).
        $mpToken = config('services.mercadopago.access_token');
        if (is_string($mpToken) && $mpToken !== '' && ! str_starts_with($mpToken, 'stub')) {
            SDK::setAccessToken($mpToken);
        }

        //SDK::setBaseUrl("https://api.mercadopago.com/sandbox"); // Activar modo Sandbo
    }
}
