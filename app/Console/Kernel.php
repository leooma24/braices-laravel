<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reservations:release-expired')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('reservations:complete-past')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        $schedule->command('reservations:request-reviews')
            ->dailyAt('10:00')
            ->withoutOverlapping();

        $schedule->command('sitemap:generate')
            ->dailyAt('04:00')
            ->withoutOverlapping();

        // Backups (spatie/laravel-backup): clean viejos a las 02:00 + nuevo a las 02:30.
        $schedule->command('backup:clean')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('backup:run --only-db')->dailyAt('02:30')->withoutOverlapping();
        // Backup completo (DB + archivos) semanal domingo 03:30.
        $schedule->command('backup:run')->weeklyOn(0, '03:30')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
