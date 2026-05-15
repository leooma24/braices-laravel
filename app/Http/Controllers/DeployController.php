<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    /**
     * Endpoint para ejecutar tareas de deploy desde el browser sin necesidad
     * de SSH. Protegido por DEPLOY_TOKEN — solo quien sabe el token corre comandos.
     *
     * Acciones soportadas:
     *   - migrate: corre migraciones pendientes
     *   - cache:  config:cache + route:cache + view:cache
     *   - clear:  config:clear + route:clear + view:clear + cache:clear
     *   - storage-link: genera el symlink public/storage -> storage/app/public
     *   - status: muestra rutas registradas (sanity check)
     */
    public function run(Request $request)
    {
        $token = (string) $request->query('token', '');
        $configured = (string) env('DEPLOY_TOKEN', '');

        if ($configured === '') {
            return response('DEPLOY_TOKEN no está configurado en .env. Por seguridad, este endpoint está deshabilitado.', 503);
        }

        if (!hash_equals($configured, $token)) {
            return response('Forbidden', 403);
        }

        $action = $request->query('action', 'status');
        $output = '';

        try {
            switch ($action) {
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    $output = Artisan::output();
                    break;

                case 'cache':
                    Artisan::call('config:cache');
                    $output .= "[config:cache]\n" . Artisan::output() . "\n";
                    Artisan::call('route:cache');
                    $output .= "[route:cache]\n" . Artisan::output() . "\n";
                    Artisan::call('view:cache');
                    $output .= "[view:cache]\n" . Artisan::output() . "\n";
                    break;

                case 'clear':
                    foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
                        Artisan::call($cmd);
                        $output .= "[{$cmd}]\n" . Artisan::output() . "\n";
                    }
                    break;

                case 'storage-link':
                    Artisan::call('storage:link');
                    $output = Artisan::output();
                    break;

                case 'sitemap':
                    Artisan::call('sitemap:generate');
                    $output = Artisan::output();
                    break;

                case 'down':
                    // Modo mantenimiento: el sitio sirve 503 con la página de "back soon"
                    // a todos los visitantes, excepto a IPs/tokens en el bypass.
                    Artisan::call('down', [
                        '--render' => 'errors::503',
                        '--retry' => 60,
                    ]);
                    $output = "Sitio en mantenimiento.\n" . Artisan::output();
                    break;

                case 'up':
                    Artisan::call('up');
                    $output = "Sitio reactivado.\n" . Artisan::output();
                    break;

                case 'status':
                    $maintenance = app()->isDownForMaintenance() ? 'on' : 'off';
                    $output = "OK\nLaravel " . app()->version() . "\nEnv: " . app()->environment() . "\nDebug: " . (config('app.debug') ? 'on' : 'off') . "\nMaintenance: {$maintenance}\n";
                    break;

                default:
                    return response('Acción no reconocida. Usa: migrate, cache, clear, storage-link, sitemap, down, up, status', 400);
            }
        } catch (\Throwable $e) {
            return response("ERROR ejecutando '{$action}':\n" . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString(), 500)
                ->header('Content-Type', 'text/plain');
        }

        return response("OK [{$action}]\n\n{$output}", 200)
            ->header('Content-Type', 'text/plain');
    }
}
