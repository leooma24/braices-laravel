<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'check_in_date',
        'check_out_date',
        'guests',
        'status',
        'nights',
        'subtotal',
        'cleaning_fee_snapshot',
        'total_price',
        'expires_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'expires_at' => 'datetime',
        'status' => ReservationStatus::class,
        'subtotal' => 'decimal:2',
        'cleaning_fee_snapshot' => 'decimal:2',
        'total_price' => 'decimal:2',
        'guests' => 'integer',
        'nights' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /** Reservaciones que bloquean fechas (pendientes y confirmadas). */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', ReservationStatus::blocking());
    }

    /** Reservaciones cuyas fechas se solapan con el rango dado. */
    public function scopeOverlapping(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn);
    }

    public function scopeForProperty(Builder $query, int $propertyId): Builder
    {
        return $query->where('property_id', $propertyId);
    }

    public function isCancellable(): bool
    {
        if (!in_array($this->status, [ReservationStatus::Pendiente, ReservationStatus::Confirmada], true)) {
            return false;
        }
        return $this->check_in_date && $this->check_in_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === ReservationStatus::Pendiente
            && $this->expires_at
            && $this->expires_at->isPast();
    }
}
