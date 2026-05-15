<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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

                case 'sync-migrations':
                    // Reconcilia el tracker de migraciones con el schema real.
                    // Para cada migration file cuya fecha es ANTES del corte
                    // (2024-10-01), marca como "already ran" en la tabla
                    // `migrations` SIN ejecutarla. Útil cuando el schema fue
                    // construido a mano vía phpMyAdmin antes de tener Laravel
                    // migrations bien configuradas. Después de esto, `migrate`
                    // sólo procesa las migraciones nuevas (idempotentes).
                    $cutoff = '2024_10_01';
                    $migrationFiles = glob(database_path('migrations/*.php'));
                    $alreadyTracked = DB::table('migrations')->pluck('migration')->toArray();
                    $marked = [];
                    $kept = [];
                    foreach ($migrationFiles as $f) {
                        $name = basename($f, '.php');
                        if (in_array($name, $alreadyTracked, true)) {
                            continue;
                        }
                        if (strncmp($name, $cutoff, strlen($cutoff)) < 0) {
                            DB::table('migrations')->insert(['migration' => $name, 'batch' => 1]);
                            $marked[] = $name;
                        } else {
                            $kept[] = $name;
                        }
                    }
                    $output = "Marcadas como ya-corridas (pre-{$cutoff}): " . count($marked) . "\n";
                    foreach ($marked as $m) {
                        $output .= "  ✓ {$m}\n";
                    }
                    $output .= "\nPendientes para ejecutar con 'migrate' (post-{$cutoff}): " . count($kept) . "\n";
                    foreach ($kept as $k) {
                        $output .= "  → {$k}\n";
                    }
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

                case 'audit-users':
                    // Lista usuarios sin propiedades — incluyendo señales para
                    // identificar bots: dominios sospechosos, sin teléfono,
                    // sin paquete asignado, sin verificar correo.
                    $orphans = \App\Models\User::query()
                        ->withCount('properties')
                        ->having('properties_count', '=', 0)
                        ->orderBy('id')
                        ->get();

                    $output = "Total usuarios sin propiedades: " . $orphans->count() . "\n\n";
                    $output .= str_pad('ID', 6) . str_pad('NOMBRE', 28) . str_pad('EMAIL', 40) . str_pad('TEL', 16) . str_pad('VERIF', 7) . str_pad('PKGS', 6) . "CREATED\n";
                    $output .= str_repeat('-', 120) . "\n";

                    $suspectDomains = ['.ru', '.cn', '.xyz', '.top', '.tk', '.ml', '.ga', '.cf', '.gq'];
                    $suspectCount = 0;

                    foreach ($orphans as $u) {
                        $isAdmin = $u->hasRole('admin');
                        if ($isAdmin) continue; // nunca tocar admins

                        $email = (string) $u->email;
                        $name = (string) ($u->name ?? '');
                        $phone = $u->phone_number ? 'sí' : '—';
                        $verified = $u->email_verified_at ? 'sí' : 'NO';
                        $pkgs = $u->userPackages()->count();

                        // Señales de bot
                        $isBot = false;
                        foreach ($suspectDomains as $d) {
                            if (str_ends_with(strtolower($email), $d)) { $isBot = true; break; }
                        }
                        if (preg_match('/[А-Яа-я\x{4e00}-\x{9fff}]/u', $name)) $isBot = true;
                        if (preg_match('/^[a-z0-9]{8,}@/', $email)) $isBot = true;
                        if (preg_match('/^[a-z]+[0-9]{4,}@/', $email) && !$u->phone_number) $isBot = true;

                        $marker = $isBot ? ' [BOT?]' : '';
                        if ($isBot) $suspectCount++;

                        $output .= str_pad('#' . $u->id, 6)
                                . str_pad(mb_substr($name, 0, 26), 28)
                                . str_pad(mb_substr($email, 0, 38), 40)
                                . str_pad($phone, 16)
                                . str_pad($verified, 7)
                                . str_pad((string) $pkgs, 6)
                                . ($u->created_at?->format('Y-m-d') ?? '—')
                                . $marker . "\n";
                    }

                    $output .= "\nSospechosos marcados [BOT?]: {$suspectCount}\n";
                    $output .= "Para borrar: action=delete-bot-users&dry_run=1 (preview) / dry_run=0 (real)\n";
                    break;

                case 'delete-bot-users':
                    // Borra usuarios sin propiedades que coincidan con señales
                    // de bot. dry_run=1 (default) sólo muestra qué borraría.
                    $dryRun = $request->query('dry_run', '1') === '1';
                    $suspectDomains = ['.ru', '.cn', '.xyz', '.top', '.tk', '.ml', '.ga', '.cf', '.gq'];

                    $orphans = \App\Models\User::query()
                        ->withCount('properties')
                        ->having('properties_count', '=', 0)
                        ->get();

                    $toDelete = [];
                    foreach ($orphans as $u) {
                        if ($u->hasRole('admin')) continue;

                        $email = strtolower((string) $u->email);
                        $name = (string) ($u->name ?? '');
                        $isBot = false;
                        foreach ($suspectDomains as $d) {
                            if (str_ends_with($email, $d)) { $isBot = true; break; }
                        }
                        if (preg_match('/[А-Яа-я\x{4e00}-\x{9fff}]/u', $name)) $isBot = true;
                        if (preg_match('/^[a-z0-9]{8,}@/', $email)) $isBot = true;
                        if (preg_match('/^[a-z]+[0-9]{4,}@/', $email) && !$u->phone_number) $isBot = true;

                        if ($isBot) $toDelete[] = $u;
                    }

                    $output = ($dryRun ? "DRY RUN — sin tocar nada\n" : "BORRANDO usuarios sospechosos\n");
                    $output .= "Total a borrar: " . count($toDelete) . "\n\n";

                    $deleted = 0;
                    foreach ($toDelete as $u) {
                        $output .= "  #{$u->id} {$u->name} <{$u->email}>\n";
                        if (!$dryRun) {
                            // Borrar dependencias primero (FKs)
                            DB::table('user_packages')->where('user_id', $u->id)->delete();
                            $u->roles()->detach();
                            $u->delete();
                            $deleted++;
                        }
                    }

                    if (!$dryRun) {
                        $output .= "\nBorrados: {$deleted}\n";
                    } else {
                        $output .= "\nPara ejecutar de verdad: agrega &dry_run=0\n";
                    }
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
