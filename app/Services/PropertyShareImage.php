<?php

namespace App\Services;

/**
 * Imagen vertical (1080x1920) para compartir una propiedad en estados de
 * WhatsApp / historias. Solo depende de GD con FreeType.
 */
class PropertyShareImage
{
    public const WIDTH = 1080;

    public const HEIGHT = 1920;

    private const PHOTO_HEIGHT = 1100;

    private const PAD = 64;

    private const NAVY = [11, 37, 64];

    private const CARD = [24, 58, 92];

    private const CORAL = [255, 127, 80];

    private const MUTED = [159, 195, 230];

    private const WHITE = [255, 255, 255];

    public function __construct(
        private string $fontMedium,
        private string $fontBlack,
        private ?string $logoPath = null,
    ) {}

    /**
     * @param  array{
     *     photo: ?string,
     *     operation: ?string,
     *     title: string,
     *     price: string,
     *     location: ?string,
     *     stats: array<int, array{value: string, label: string}>,
     *     phone: ?string,
     *     site: string,
     * }  $data  Los valores de stats pueden terminar en "²" (se dibuja como superíndice).
     * @return string JPEG en binario
     */
    public function render(array $data): string
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($img, true);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $this->color($img, self::NAVY));

        $this->drawPhoto($img, $data['photo']);
        $this->drawGradient($img, 0, 260, self::NAVY, 0.55, true);
        $this->drawGradient($img, self::PHOTO_HEIGHT - 560, self::PHOTO_HEIGHT, self::NAVY, 1.0);

        $this->drawHeader($img, $data['operation']);
        $this->drawTitle($img, $data['title']);

        $y = self::PHOTO_HEIGHT + 40;
        if ($data['location']) {
            imagefilledellipse($img, self::PAD + 12, $y + 26, 22, 22, $this->color($img, self::CORAL));
            $this->text($img, $data['location'], 30, $this->fontMedium, self::PAD + 44, $y + 40, self::MUTED);
        }

        $this->drawPrice($img, $data['price'], $y + 200);
        $this->drawStats($img, $data['stats'], $y + 250);
        $this->drawFooter($img, $data['phone'], $data['site']);

        ob_start();
        imagejpeg($img, null, 88);
        $bytes = ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    private function drawPhoto($img, ?string $path): void
    {
        $src = ($path && is_file($path)) ? @imagecreatefromstring((string) file_get_contents($path)) : false;

        if (! $src) {
            imagefilledrectangle($img, 0, 0, self::WIDTH, self::PHOTO_HEIGHT, $this->color($img, [0, 81, 135]));

            return;
        }

        // Cover: escalar para llenar el área y recortar al centro.
        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = max(self::WIDTH / $sw, self::PHOTO_HEIGHT / $sh);
        $cropW = (int) round(self::WIDTH / $scale);
        $cropH = (int) round(self::PHOTO_HEIGHT / $scale);

        imagecopyresampled(
            $img, $src, 0, 0,
            (int) (($sw - $cropW) / 2), (int) (($sh - $cropH) / 2),
            self::WIDTH, self::PHOTO_HEIGHT, $cropW, $cropH
        );
        imagedestroy($src);
    }

    private function drawGradient($img, int $y0, int $y1, array $rgb, float $maxOpacity, bool $fromTop = false): void
    {
        $height = $y1 - $y0;
        for ($i = 0; $i < $height; $i++) {
            $t = $fromTop ? 1 - $i / $height : $i / $height;
            $alpha = (int) round(127 - 127 * $maxOpacity * ($t ** 1.6));
            imageline($img, 0, $y0 + $i, self::WIDTH, $y0 + $i, imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha));
        }
    }

    private function drawHeader($img, ?string $operation): void
    {
        $pillH = 96;
        $logo = ($this->logoPath && is_file($this->logoPath)) ? @imagecreatefrompng($this->logoPath) : false;

        if ($logo) {
            $logoH = 56;
            $logoW = (int) round(imagesx($logo) * $logoH / imagesy($logo));
            $this->roundedRect($img, self::PAD, self::PAD, self::PAD + $logoW + 64, self::PAD + $pillH, $pillH / 2, self::WHITE);
            imagecopyresampled($img, $logo, self::PAD + 32, self::PAD + 20, 0, 0, $logoW, $logoH, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
        }

        if ($operation) {
            $label = mb_strtoupper($operation);
            $w = $this->textWidth($label, 30, $this->fontBlack) + 80;
            $x2 = self::WIDTH - self::PAD;
            $this->roundedRect($img, $x2 - $w, self::PAD, $x2, self::PAD + $pillH, $pillH / 2, self::CORAL);
            $this->text($img, $label, 30, $this->fontBlack, $x2 - $w + 40, self::PAD + 64, self::WHITE);
        }
    }

    private function drawTitle($img, string $title): void
    {
        $maxWidth = self::WIDTH - self::PAD * 2;

        // Se reduce el tamaño hasta que quepa en 2 líneas; al mínimo se trunca.
        foreach ([50, 46, 42, 38] as $size) {
            if (count($this->wrap($title, $size, $this->fontBlack, $maxWidth, PHP_INT_MAX)) <= 2) {
                break;
            }
        }

        $lines = $this->wrap($title, $size, $this->fontBlack, $maxWidth, 2);
        $lineHeight = (int) round($size * 1.55);
        $baseline = self::PHOTO_HEIGHT - 40 - ($lineHeight * (count($lines) - 1));

        foreach ($lines as $line) {
            $this->text($img, $line, $size, $this->fontBlack, self::PAD, $baseline, self::WHITE);
            $baseline += $lineHeight;
        }
    }

    private function drawPrice($img, string $price, int $baseline): void
    {
        // Precios largos ($123,000,000) se reducen para que "MXN" no se salga del margen.
        $maxWidth = self::WIDTH - self::PAD * 2 - 20 - $this->textWidth('MXN', 30, $this->fontMedium);
        $size = 96;
        while ($size > 60 && $this->textWidth($price, $size, $this->fontBlack) > $maxWidth) {
            $size -= 4;
        }

        $this->text($img, $price, $size, $this->fontBlack, self::PAD, $baseline, self::WHITE);
        $x = self::PAD + $this->textWidth($price, $size, $this->fontBlack) + 20;
        $this->text($img, 'MXN', 30, $this->fontMedium, $x, $baseline, self::MUTED);
    }

    private function drawStats($img, array $stats, int $top): void
    {
        $stats = array_slice($stats, 0, 4);
        if (! $stats) {
            return;
        }

        $gap = 20;
        $height = 200;
        $width = (int) ((self::WIDTH - self::PAD * 2 - $gap * (count($stats) - 1)) / count($stats));

        foreach ($stats as $i => $stat) {
            $x1 = self::PAD + $i * ($width + $gap);
            $this->roundedRect($img, $x1, $top, $x1 + $width, $top + $height, 28, self::CARD);
            $this->drawStatValue($img, $stat['value'], $x1, $width, $top + 100);
            $this->centeredText($img, $stat['label'], 24, $this->fontMedium, $x1, $width, $top + 156, self::MUTED);
        }
    }

    private function drawStatValue($img, string $value, int $x, int $width, int $baseline): void
    {
        // Metropolis no trae el glifo "²": se dibuja un "2" chico como superíndice.
        $hasSup = str_ends_with($value, '²');
        $base = $hasSup ? mb_substr($value, 0, -1) : $value;

        $size = 44;
        while (true) {
            $supSize = $size * 0.5;
            $baseWidth = $this->textWidth($base, $size, $this->fontBlack);
            $total = $baseWidth + ($hasSup ? 4 + $this->textWidth('2', $supSize, $this->fontBlack) : 0);
            if ($total <= $width - 28 || $size <= 28) {
                break;
            }
            $size -= 2;
        }

        $startX = $x + (int) (($width - $total) / 2);
        $this->text($img, $base, $size, $this->fontBlack, $startX, $baseline, self::WHITE);

        if ($hasSup) {
            $this->text($img, '2', $supSize, $this->fontBlack, $startX + $baseWidth + 4, $baseline - (int) round($size * 0.62), self::WHITE);
        }
    }

    private function drawFooter($img, ?string $phone, string $site): void
    {
        $top = 1640;
        $this->roundedRect($img, self::PAD, $top, self::WIDTH - self::PAD, $top + 180, 36, self::CORAL);

        if ($phone) {
            $this->centeredText($img, 'INFORMES Y CITAS', 24, $this->fontMedium, 0, self::WIDTH, $top + 62, self::WHITE);
            $this->centeredText($img, $phone, 54, $this->fontBlack, 0, self::WIDTH, $top + 140, self::WHITE);
        } else {
            $this->centeredText($img, 'PIDE INFORMES', 54, $this->fontBlack, 0, self::WIDTH, $top + 115, self::WHITE);
        }

        $this->centeredText($img, $site, 28, $this->fontMedium, 0, self::WIDTH, self::HEIGHT - 44, self::MUTED);
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

    private function centeredText($img, string $text, float $size, string $font, int $x, int $width, int $baseline, array $rgb): void
    {
        $this->text($img, $text, $size, $font, $x + (int) (($width - $this->textWidth($text, $size, $font)) / 2), $baseline, $rgb);
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
            $last = $lines[$maxLines - 1];
            while ($last !== '' && $this->textWidth($last.'...', $size, $font) > $maxWidth) {
                $last = mb_substr($last, 0, -1);
            }
            $lines[$maxLines - 1] = rtrim($last).'...';
        }

        return $lines;
    }

    private function color($img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }
}
