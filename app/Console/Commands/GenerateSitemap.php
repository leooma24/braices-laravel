<?php

namespace App\Console\Commands;

use App\Services\SitemapBuilder;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Genera public/sitemap.xml (en prod se sirve dinámico desde la ruta /sitemap.xml).';

    public function handle(SitemapBuilder $builder): int
    {
        // Ojo: en CLI las URLs salen de APP_URL. Un public/sitemap.xml generado con un
        // APP_URL incorrecto tapa la ruta dinámica, por eso no se agenda en prod.
        $builder->build()->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generado en public/sitemap.xml');

        return self::SUCCESS;
    }
}
