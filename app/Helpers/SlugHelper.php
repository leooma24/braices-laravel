<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SlugHelper
{
    public static function createUniqueSlug($name, $model)
    {
        // Generar el slug base
        $slug = Str::slug($name);
        $originalSlug = $slug;
        // Comprobar si el slug ya existe
        $count = 1;

        while ($model::where('slug', $slug)->exists()) {
            // Añadir un sufijo numérico si ya existe
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
