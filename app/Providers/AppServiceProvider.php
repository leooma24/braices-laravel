<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
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

        // Solo configuramos el SDK si el token está presente y parece real.
        // Llamar SDK::setAccessToken con un valor stub o vacío hace que el
        // SDK intente validar contra el endpoint de MP, lo cual:
        //  - rompe `artisan package:discover` en GitHub Actions (PolicyAgent
        //    bloquea ese egress), abortando el deploy antes de tocar FTP.
        //  - mete latencia innecesaria en cualquier artisan command corrido
        //    sin credenciales (tests, CI, migraciones locales).
        $mpToken = env('MERCADO_PAGO_ACCESS_TOKEN');
        if (is_string($mpToken) && $mpToken !== '' && !str_starts_with($mpToken, 'stub')) {
            SDK::setAccessToken($mpToken);
        }

        //SDK::setBaseUrl("https://api.mercadopago.com/sandbox"); // Activar modo Sandbo
    }
}
