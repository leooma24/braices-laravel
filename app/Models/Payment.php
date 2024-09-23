<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'external_reference',
        'payment_id',
        'user_id',
        'package_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
