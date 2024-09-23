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

        SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));

        //SDK::setBaseUrl("https://api.mercadopago.com/sandbox"); // Activar modo Sandbo
    }
}
