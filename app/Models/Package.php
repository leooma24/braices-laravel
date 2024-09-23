<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_listings',
        'price',
        'duration',
        'characteristics'
    ];

    protected $casts = [
        'characteristics' => 'array'
    ];

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }
}
