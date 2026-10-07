<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Averigua de dónde llegó una visita a una ficha y lo suma a la cuenta del día.
 *
 * El orden importa: primero la marca que Omar pega a mano en los grupos
 * (`?de=facebook`), porque es la única señal confiable. Facebook manda a mucha
 * gente sin `Referer` (la app del celular casi nunca lo manda), así que si se
 * dependiera solo del referer, buena parte del tráfico de los grupos se contaría
 * como "directo" y parecería que las publicaciones no sirven.
 */
class VisitSource
{
    /** Mapea el dominio de procedencia a un nombre corto. */
    private const HOSTS = [
        'facebook' => 'facebook',
        'fb.com' => 'facebook',
        'instagram' => 'instagram',
        'google' => 'google',
        'bing' => 'bing',
        'duckduckgo' => 'google',
        'whatsapp' => 'whatsapp',
        't.co' => 'twitter',
        'twitter' => 'twitter',
    ];

    public function record(Property $property, Request $request): void
    {
        if ($this->looksLikeBot($request)) {
            return;
        }

        DB::table('property_view_sources')->upsert(
            [[
                'property_id' => $property->id,
                'source' => $this->detect($request),
                'day' => now()->toDateString(),
                'views' => 1,
            ]],
            ['property_id', 'source', 'day'],
            // La columna se incrementa en vez de pisarse.
            ['views' => DB::raw('views + 1')]
        );
    }

    public function detect(Request $request): string
    {
        $tag = $request->query('de');
        if (is_string($tag) && $tag !== '') {
            return $this->clean($tag);
        }

        // Compatibilidad con ligas que ya traigan utm_source.
        $utm = $request->query('utm_source');
        if (is_string($utm) && $utm !== '') {
            return $this->clean($utm);
        }

        $referer = (string) $request->headers->get('referer');
        if ($referer === '') {
            return 'directo';
        }

        $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
        if ($host === '') {
            return 'directo';
        }

        if (str_contains($host, (string) parse_url(config('app.url'), PHP_URL_HOST) ?: 'bienescorp')
            || str_contains($host, 'bienescorp')) {
            return 'interno';
        }

        foreach (self::HOSTS as $needle => $name) {
            if (str_contains($host, $needle)) {
                return $name;
            }
        }

        return 'otro';
    }

    private function clean(string $value): string
    {
        $value = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $value) ?? '');

        return substr($value, 0, 30) ?: 'otro';
    }

    /**
     * Filtro ligero. No pretende atrapar todo: solo evitar que los rastreadores
     * mas obvios inflen el conteo de un origen.
     */
    private function looksLikeBot(Request $request): bool
    {
        $agent = strtolower((string) $request->userAgent());

        if ($agent === '') {
            return true;
        }

        foreach (['bot', 'crawler', 'spider', 'slurp', 'facebookexternalhit', 'preview', 'headless'] as $needle) {
            if (str_contains($agent, $needle)) {
                return true;
            }
        }

        return false;
    }
}
