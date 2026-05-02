<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class CompletePastReservations extends Command
{
    protected $signature = 'reservations:complete-past';

    protected $description = 'Marca como completadas las reservaciones confirmadas cuyo check-out ya pasó.';

    public function handle(): int
    {
        $count = Reservation::query()
            ->where('status', ReservationStatus::Confirmada->value)
            ->where('check_out_date', '<', today())
            ->update([
                'status' => ReservationStatus::Completada->value,
                'updated_at' => now(),
            ]);

        $this->info("Reservaciones completadas: {$count}");
        return self::SUCCESS;
    }
}
