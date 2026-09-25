<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /**
     * Estados por los que pasa un prospecto, en orden de avance.
     */
    public const ESTADOS = [
        'nuevo' => 'Nuevo',
        'contestado' => 'Contestado',
        'cita' => 'Cita agendada',
        'cerrado' => 'Cerrado',
        'descartado' => 'Descartado',
    ];

    /**
     * Horas sin contestar antes de marcar el prospecto como urgente.
     */
    public const HORAS_PARA_URGENTE = 2;

    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'source',
        'status',
        'responded_at',
        'notes',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'nuevo';
    }

    /**
     * Un prospecto nuevo que ya lleva demasiado tiempo sin respuesta.
     */
    public function isUrgent(): bool
    {
        return $this->isPending()
            && $this->created_at !== null
            && $this->created_at->diffInHours(now()) >= self::HORAS_PARA_URGENTE;
    }

    public function statusLabel(): string
    {
        return self::ESTADOS[$this->status] ?? $this->status;
    }

    /**
     * Liga de WhatsApp con el mensaje ya escrito para contestarle.
     */
    public function whatsappUrl(): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $this->phone);

        if ($phone === '') {
            return null;
        }

        if (strlen($phone) === 10) {
            $phone = '52'.$phone;
        }

        $texto = $this->property
            ? 'Hola '.$this->name.', te contesto por la propiedad: '.$this->property->title
            : 'Hola '.$this->name.', te contesto por tu mensaje en BienesCorp';

        return 'https://wa.me/'.$phone.'?text='.urlencode($texto);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'nuevo');
    }

    /**
     * Prospectos que puede ver este usuario: el admin ve todo,
     * el asesor solo los de sus propiedades.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
