<?php

namespace App\Notifications\Reservation;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedHost extends Notification
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
        $r = $this->reservation->loadMissing('property', 'user');
        $guest = $r->user;
        $property = $r->property;

        return (new MailMessage)
            ->subject("Nueva reservación confirmada: {$property->title}")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Tienes una nueva reservación confirmada y pagada.")
            ->line("**Propiedad:** {$property->title}")
            ->line("**Huésped:** {$guest->name} ({$guest->email})")
            ->when($guest->phone_number, fn ($m) => $m->line("**Teléfono del huésped:** {$guest->phone_number}"))
            ->line("**Check-in:** {$r->check_in_date->format('d/m/Y')}")
            ->line("**Check-out:** {$r->check_out_date->format('d/m/Y')}")
            ->line("**Huéspedes:** {$r->guests}")
            ->line("**Monto cobrado:** \${$r->total_price}")
            ->action('Ver reservas recibidas', route('host.reservations'))
            ->line('Recuerda preparar la propiedad antes del check-in.');
    }
}
