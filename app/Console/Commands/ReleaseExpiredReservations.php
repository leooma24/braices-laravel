<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Notifications\Reservation\ReservationCancelled;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'reservations:release-expired';

    protected $description = 'Cancela reservaciones pendientes que pasaron su tiempo de expiración sin pago.';

    public function handle(): int
    {
        $expired = Reservation::query()
            ->where('status', ReservationStatus::Pendiente->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('user', 'property.user')
            ->get();

        $count = 0;
        foreach ($expired as $reservation) {
            $reservation->update([
                'status' => ReservationStatus::Cancelada,
                'expires_at' => null,
            ]);

            try {
                $reservation->user?->notify(new ReservationCancelled($reservation, 'system'));
            } catch (\Throwable $e) {
                $this->warn("No se pudo notificar la expiración de la reserva {$reservation->id}: {$e->getMessage()}");
            }

            $count++;
        }

        $this->info("Reservaciones expiradas liberadas: {$count}");
        return self::SUCCESS;
    }
}
