<?php

namespace App\Notifications\Reservation;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestReview extends Notification
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
        $r = $this->reservation->loadMissing('property');

        return (new MailMessage)
            ->subject("¿Cómo fue tu estadía en {$r->property->title}?")
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Esperamos que hayas disfrutado tu estadía en **{$r->property->title}**.")
            ->line('Tu opinión ayuda a otros huéspedes a elegir mejor y al anfitrión a mejorar.')
            ->action('Dejar reseña', route('my.reservations'))
            ->line('Gracias por usar BienesCorp.');
    }
}
