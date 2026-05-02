<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
    case Rechazada = 'rechazada';
    case Completada = 'completada';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente de pago',
            self::Confirmada => 'Confirmada',
            self::Cancelada => 'Cancelada',
            self::Rechazada => 'Rechazada',
            self::Completada => 'Completada',
        };
    }

    /** Estados que ocupan fechas (bloquean disponibilidad). */
    public static function blocking(): array
    {
        return [self::Pendiente->value, self::Confirmada->value];
    }
}
