<?php

namespace App\Notifications\Reservation;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCancelled extends Notification
{
    use Queueable;

    public function __construct(
        public Reservation $reservation,
        public string $cancelledBy = 'guest', // 'guest' | 'host' | 'system'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->reservation->loadMissing('property');
        $property = $r->property;

        $reason = match ($this->cancelledBy) {
            'host' => 'El anfitrión canceló esta reservación.',
            'system' => 'La reservación se canceló automáticamente por falta de pago.',
            default => 'Se canceló la reservación.',
        };

        return (new MailMessage)
            ->subject("Reservación cancelada: {$property->title}")
            ->greeting("Hola, {$notifiable->name}")
            ->line($reason)
            ->line("**Propiedad:** {$property->title}")
            ->line("**Fechas:** {$r->check_in_date->format('d/m/Y')} → {$r->check_out_date->format('d/m/Y')}")
            ->when($this->cancelledBy !== 'system', fn ($m) => $m->line("Si tienes dudas sobre el reembolso, contáctanos."));
    }
}
