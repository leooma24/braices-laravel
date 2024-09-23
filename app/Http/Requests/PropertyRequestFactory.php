<?php
// app/Http/Requests/PropertyRequestFactory.php
namespace App\Http\Requests;

use App\Models\Property;
use InvalidArgumentException;
use Illuminate\Support\Str;

class PropertyRequestFactory
{
    public static function make(string $propertyType)
    {
        $propertyType = Str::lower($propertyType);
        if(Str::contains($propertyType, 'terreno')) {
            return new LandRequest();
        } else {
            return new PropertyRequest();
        }

    }
}
