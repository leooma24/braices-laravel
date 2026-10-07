<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Descarta envíos de formulario que parecen de bot, usando el par de campos
 * que pinta <x-honeypot />.
 *
 * Al bot se le responde con el mismo "mensaje enviado" que a una persona: si se
 * le devuelve un error, quien lo opera lo nota y ajusta el script. Callando, el
 * bot cree que funcionó y se sigue de largo.
 */
class Honeypot
{
    /** Un formulario real no se llena y envía en menos de esto. */
    private const MIN_SECONDS = 3;

    public function handle(Request $request, Closure $next): Response
    {
        if ($reason = $this->looksAutomated($request)) {
            Log::info('Envío descartado por honeypot', [
                'motivo' => $reason,
                'ruta' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return back()->with('success', 'Tu mensaje ha sido enviado correctamente');
        }

        return $next($request);
    }

    private function looksAutomated(Request $request): ?string
    {
        if (filled($request->input('direccion_alterna'))) {
            return 'campo trampa lleno';
        }

        $stamp = $request->input('hp_ts');

        // Sin marca de tiempo no se castiga: puede ser una vista vieja en caché
        // o un formulario al que todavía no se le agregó el componente.
        if (! $stamp) {
            return null;
        }

        try {
            $rendered = (int) Crypt::decrypt($stamp);
        } catch (\Throwable) {
            return 'marca de tiempo alterada';
        }

        if (time() - $rendered < self::MIN_SECONDS) {
            return 'enviado demasiado rápido';
        }

        return null;
    }
}
