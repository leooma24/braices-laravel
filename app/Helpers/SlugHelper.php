<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SlugHelper
{
    /**
     * $ignoreId es el registro que se esta editando. Sin el, al reguardar un
     * perfil el slug chocaba consigo mismo y le colgaba un "-1"; a la
     * siguiente guardada el "-1" quedaba libre y volvia al original, asi que
     * la URL publica del usuario brincaba en cada guardada y rompia las ligas
     * ya compartidas (iframes incluidos).
     */
    public static function createUniqueSlug($name, $model, $ignoreId = null)
    {
        // Generar el slug base
        $slug = Str::slug($name);
        $originalSlug = $slug;
        // Comprobar si el slug ya existe
        $count = 1;

        while (self::slugTaken($model, $slug, $ignoreId)) {
            // Añadir un sufijo numérico si ya existe
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    private static function slugTaken($model, string $slug, $ignoreId): bool
    {
        $query = $model::where('slug', $slug);

        if (!is_null($ignoreId)) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
