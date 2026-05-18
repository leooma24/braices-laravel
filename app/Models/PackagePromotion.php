<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'label',
        'discount_percent',
        'from_count',
        'to_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_percent' => 'integer',
        'from_count' => 'integer',
        'to_count' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
