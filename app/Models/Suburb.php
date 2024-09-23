<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suburb extends Model
{
    use HasFactory;

    protected $table = 'colonias';

    protected $fillable = ['nombre', 'ciudad', 'municipio', 'codigo_postal'];

    public function township()
    {
        return $this->belongsTo(Township::class, 'id', 'municipio');
    }
}
