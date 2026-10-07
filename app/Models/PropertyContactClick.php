<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyContactClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['property_id', 'channel'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
