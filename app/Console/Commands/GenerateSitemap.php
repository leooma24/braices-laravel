<?php

namespace App\Console\Commands;

use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Genera sitemap.xml con páginas estáticas y propiedades activas.';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        $staticUrls = [
            ['/', Url::CHANGE_FREQUENCY_DAILY, 1.0],
            ['/propiedades', Url::CHANGE_FREQUENCY_DAILY, 0.9],
            ['/reservaciones', Url::CHANGE_FREQUENCY_DAILY, 0.9],
            ['/nosotros', Url::CHANGE_FREQUENCY_MONTHLY, 0.5],
            ['/contacto', Url::CHANGE_FREQUENCY_MONTHLY, 0.5],
            ['/planes', Url::CHANGE_FREQUENCY_WEEKLY, 0.6],
        ];

        foreach ($staticUrls as [$path, $freq, $priority]) {
            $sitemap->add(
                Url::create($path)
                    ->setChangeFrequency($freq)
                    ->setPriority($priority)
                    ->setLastModificationDate(Carbon::now())
            );
        }

        Property::where('property_status_id', 1)
            ->orderBy('updated_at', 'desc')
            ->chunk(200, function ($properties) use ($sitemap) {
                foreach ($properties as $property) {
                    $sitemap->add(
                        Url::create('/propiedad/' . $property->slug)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                            ->setLastModificationDate($property->updated_at ?? Carbon::now())
                    );
                }
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generado en public/sitemap.xml');
        return self::SUCCESS;
    }
}
