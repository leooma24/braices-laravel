<?php

namespace App\Notifications\Reservation;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedGuest extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->reservation->loadMissing('property.user');
        $property = $r->property;
        $host = $property->user;

        return (new MailMessage)
            ->subject("Tu reservación en {$property->title} fue confirmada")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Tu reservación quedó confirmada. Aquí los detalles:")
            ->line("**Propiedad:** {$property->title}")
            ->line("**Check-in:** {$r->check_in_date->format('d/m/Y')}")
            ->line("**Check-out:** {$r->check_out_date->format('d/m/Y')}")
            ->line("**Huéspedes:** {$r->guests}")
            ->line("**Total pagado:** \${$r->total_price}")
            ->when($host?->phone_number, fn ($m) => $m->line("**Contacto del anfitrión:** {$host->phone_number}"))
            ->action('Ver mi reservación', route('my.reservations'))
            ->line('¡Que disfrutes tu estadía!');
    }
}
