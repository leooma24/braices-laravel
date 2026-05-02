<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    use HasFactory;

    protected $table = 'pricing';

    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'date',
        'price_per_night',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
