<?php

namespace App\Services;

use Anthropic\Anthropic;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class AIDescriptionService
{
    private const MODEL = 'claude-opus-4-7';
    private const MAX_TOKENS = 1024;

    /**
     * Genera una descripción profesional en español neutro para una propiedad.
     *
     * @param  array<string, mixed>  $propertyData  campos básicos: title, address,
     *         city, state, bedrooms, bathrooms, square_feet, year_built, lot_size,
     *         price, transaction (venta/renta), property_types (array de nombres),
     *         amenities (array opcional), is_reservable (bool), max_guests, etc.
     */
    public function generate(array $propertyData): string
    {
        if (!env('ANTHROPIC_API_KEY')) {
            throw new RuntimeException('ANTHROPIC_API_KEY no está configurado en .env');
        }

        $client = Anthropic::client(env('ANTHROPIC_API_KEY'));

        $userPrompt = $this->buildUserPrompt($propertyData);
        $systemPrompt = $this->buildSystemPrompt();

        // Cache prefix-match: el system prompt se cachea para abaratar
        // requests repetidas; cache_control va en el último bloque del system.
        $response = $client->messages()->create([
            'model' => self::MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'system' => [
                [
                    'type' => 'text',
                    'text' => $systemPrompt,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
        ]);

        // Extraer texto del primer bloque
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                return trim($block->text);
            }
        }

        throw new RuntimeException('Claude no devolvió contenido de texto.');
    }

    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
Eres un copywriter especializado en bienes raíces en México. Generas descripciones para anuncios inmobiliarios que sean:

1. **Profesionales pero cálidas**: tono cercano, sin clichés ("hogar de tus sueños", "oportunidad única"), sin exageraciones.
2. **Concretas**: mencionas datos específicos del inmueble (m², recámaras, ubicación) en lugar de vaguedades.
3. **Estructuradas**: 2 a 3 párrafos cortos, fáciles de escanear desde móvil. Sin listas con bullets ni emojis.
4. **Optimizadas para conversión**: termina con un llamado a la acción suave, sin gritos en mayúsculas.
5. **En español neutro mexicano**: evita regionalismos muy locales y modismos. Tutea al lector ("encontrarás", "vivirás").

NO inventes amenidades, ubicaciones o características que el usuario no proporcionó. Si falta información clave, omítela en lugar de inventar. NO uses palabras como "increíble", "espectacular", "maravilloso" — son ruido.

Devuelve SOLO la descripción, sin encabezados, comillas, ni texto introductorio.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildUserPrompt(array $data): string
    {
        $lines = ['Genera la descripción para esta propiedad:'];
        $lines[] = '';

        $field = function (string $label, $value) use (&$lines) {
            if (!empty($value)) {
                $lines[] = "- {$label}: {$value}";
            }
        };

        $field('Título', $data['title'] ?? null);
        $field('Tipo(s)', is_array($data['property_types'] ?? null) ? implode(', ', $data['property_types']) : null);
        $field('Transacción', $data['transaction'] ?? null);
        $field('Dirección', $data['address'] ?? null);
        $field('Ciudad', $data['city'] ?? null);
        $field('Estado', $data['state'] ?? null);
        $field('Colonia', $data['suburb'] ?? null);
        $field('Recámaras', $data['bedrooms'] ?? null);
        $field('Baños', $data['bathrooms'] ?? null);
        $field('Metros cuadrados de construcción', $data['square_feet'] ?? null);
        $field('Tamaño de terreno (m²)', $data['lot_size'] ?? null);
        $field('Frente (m)', $data['front'] ?? null);
        $field('Fondo (m)', $data['depth'] ?? null);
        $field('Año de construcción', $data['year_built'] ?? null);
        $field('Niveles', $data['levels'] ?? null);
        $field('Precio', isset($data['price']) ? '$' . number_format((float) $data['price']) . ' MXN' : null);

        if (!empty($data['is_reservable'])) {
            $lines[] = '- Disponible para reservar por noche';
            $field('Capacidad máxima', $data['max_guests'] ?? null);
            $field('Precio por noche', isset($data['price_per_night']) ? '$' . number_format((float) $data['price_per_night']) . ' MXN' : null);
        }

        if (!empty($data['amenities']) && is_array($data['amenities'])) {
            $lines[] = '- Amenidades: ' . implode(', ', $data['amenities']);
        }

        $lines[] = '';
        $lines[] = 'Genera 2-3 párrafos cortos en español neutro mexicano.';

        return implode("\n", $lines);
    }
}
