<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Notifications\Reservation\RequestReview;
use Illuminate\Console\Command;

class RequestReviews extends Command
{
    protected $signature = 'reservations:request-reviews';

    protected $description = 'Envía email pidiendo reseña a reservaciones completadas hace 24-48h sin reseña.';

    public function handle(): int
    {
        $candidates = Reservation::query()
            ->where('status', ReservationStatus::Completada->value)
            ->whereDoesntHave('review')
            ->whereBetween('check_out_date', [today()->subDays(2), today()->subDay()])
            ->with('user', 'property')
            ->get();

        $count = 0;
        foreach ($candidates as $reservation) {
            try {
                $reservation->user?->notify(new RequestReview($reservation));
                $count++;
            } catch (\Throwable $e) {
                $this->warn("No se pudo solicitar reseña para reserva {$reservation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Solicitudes de reseña enviadas: {$count}");
        return self::SUCCESS;
    }
}
