<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'link',
        'position',
        'is_active',
    ];

    public function getImagePathAttribute($value)
    {
        return $value ? asset('banners/' . $value) : null;
    }
}
