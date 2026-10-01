<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyViewSnapshot extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'views', 'captured_on'];

    protected $casts = [
        'views' => 'integer',
        'captured_on' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
