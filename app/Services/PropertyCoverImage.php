<?php

namespace App\Services;

/**
 * Portada horizontal (800x560) para las tarjetas de propiedades que no tienen
 * fotografía. Sustituye el hueco gris de "Sin fotografía" por una tarjeta de
 * marca con tipo, título, precio y medidas.
 *
 * Mismo criterio que PropertyShareImage (GD + FreeType), pero en formato
 * apaisado y sin foto: aquí la imagen ES el respaldo.
 */
class PropertyCoverImage
{
    public const WIDTH = 800;

    public const HEIGHT = 560;

    private const PAD = 48;

    private const NAVY = [11, 37, 64];

    private const NAVY_LIGHT = [24, 58, 92];

    private const CORAL = [255, 127, 80];

    private const MUTED = [159, 195, 230];

    private const WHITE = [255, 255, 255];

    public function __construct(
        private string $fontMedium,
        private string $fontBlack,
    ) {}

    /**
     * La tarjeta de la propiedad ya imprime titulo, precio y etiquetas debajo
     * de la imagen, asi que aqui NO se repiten: la portada muestra el dato que
     * mejor describe al inmueble (su medida principal) y poco mas. Repetirlo
     * todo se veia como un cartel encimado.
     *
     * @param  array{
     *     type: ?string,
     *     location: ?string,
     *     stats: array<int, array{value: string, label: string}>,
     *     site: string,
     * }  $data
     * @return string JPEG en binario
     */
    public function render(array $data): string
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($img, true);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $this->color($img, self::NAVY));

        $this->drawBackdrop($img);

        if ($data['type']) {
            $label = mb_strtoupper($data['type']);
            $this->text($img, $this->ellipsize($label, 20, $this->fontMedium, 380), 20, $this->fontMedium, self::PAD, self::PAD + 26, self::MUTED);
            imagefilledrectangle($img, self::PAD, self::PAD + 48, self::PAD + 56, self::PAD + 52, $this->color($img, self::CORAL));
        }

        $this->drawHeadline($img, $data['stats']);

        if ($data['location']) {
            $this->text(
                $img,
                $this->ellipsize($data['location'], 24, $this->fontMedium, self::WIDTH - self::PAD * 2 - 180),
                24,
                $this->fontMedium,
                self::PAD,
                self::HEIGHT - self::PAD - 8,
                self::MUTED
            );
        }

        $this->drawFooter($img, $data['site']);

        ob_start();
        imagejpeg($img, null, 86);
        $bytes = ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    /**
     * El dato grande del centro: la primera medida disponible (terreno,
     * construccion, recamaras...). Es lo unico que la tarjeta no destaca.
     *
     * @param  array<int, array{value: string, label: string}>  $stats
     */
    private function drawHeadline($img, array $stats): void
    {
        if ($stats === []) {
            return;
        }

        $main = $stats[0];
        $baseline = (int) (self::HEIGHT / 2) + 34;

        $width = $this->drawStatValue($img, $main['value'], self::PAD, $baseline, 86);
        $this->text($img, mb_strtoupper($main['label']), 20, $this->fontMedium, self::PAD + 4, $baseline + 36, self::MUTED);

        // Segunda medida al lado, si cabe sin apretarse.
        if (isset($stats[1]) && $width + 220 < self::WIDTH - self::PAD * 2) {
            $x = self::PAD + $width + 72;
            $this->drawStatValue($img, $stats[1]['value'], $x, $baseline, 40);
            $this->text($img, mb_strtoupper($stats[1]['label']), 16, $this->fontMedium, $x + 2, $baseline + 36, self::MUTED);
        }
    }

    /**
     * Dos círculos tenues en la esquina derecha: dan profundidad sin competir
     * con el texto ni parecer una foto rota.
     */
    private function drawBackdrop($img): void
    {
        $soft = $this->color($img, self::NAVY_LIGHT);
        imagefilledellipse($img, self::WIDTH - 40, 90, 420, 420, $soft);
        imagefilledellipse($img, self::WIDTH - 150, self::HEIGHT + 60, 360, 360, $soft);
    }

    /**
     * @param  array<int, array{value: string, label: string}>  $stats
     */
    private function drawFooter($img, string $site): void
    {
        $w = $this->textWidth($site, 18, $this->fontMedium);
        $this->text($img, $site, 18, $this->fontMedium, self::WIDTH - self::PAD - $w, self::HEIGHT - self::PAD + 6, self::MUTED);
    }

    /**
     * Metropolis no trae el glifo "²": se dibuja un "2" chico como superíndice,
     * igual que en la imagen para compartir.
     *
     * @return int ancho total dibujado
     */
    private function drawStatValue($img, string $value, int $x, int $baseline, float $size = 22): int
    {
        $hasSup = str_ends_with($value, '²');
        $base = $hasSup ? mb_substr($value, 0, -1) : $value;

        $this->text($img, $base, $size, $this->fontBlack, $x, $baseline, self::WHITE);
        $width = $this->textWidth($base, $size, $this->fontBlack);

        if ($hasSup) {
            $supSize = $size * 0.55;
            $this->text($img, '2', $supSize, $this->fontBlack, $x + $width + 2, $baseline - (int) round($size * 0.58), self::WHITE);
            $width += 2 + $this->textWidth('2', $supSize, $this->fontBlack);
        }

        return $width;
    }

    private function ellipsize(string $text, float $size, string $font, int $maxWidth): string
    {
        if ($this->textWidth($text, $size, $font) <= $maxWidth) {
            return $text;
        }

        while ($text !== '' && $this->textWidth($text.'...', $size, $font) > $maxWidth) {
            $text = mb_substr($text, 0, -1);
        }

        return rtrim($text).'...';
    }

    private function roundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, array $rgb): void
    {
        $c = $this->color($img, $rgb);
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $c);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $c);
        imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $c);
    }

    private function text($img, string $text, float $size, string $font, int $x, int $baseline, array $rgb): void
    {
        imagettftext($img, $size, 0, $x, $baseline, $this->color($img, $rgb), $font, $text);
    }

    private function textWidth(string $text, float $size, string $font): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return (int) abs($box[2] - $box[0]);
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, float $size, string $font, int $maxWidth, int $maxLines): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $test = $line === '' ? $word : "{$line} {$word}";
            if ($line !== '' && $this->textWidth($test, $size, $font) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $test;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = $this->ellipsize($lines[$maxLines - 1], $size, $font, $maxWidth);
        }

        return $lines;
    }

    private function color($img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }
}
