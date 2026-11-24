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
        // Backup quotidien à 2h
        $schedule->command('sicofil:backup --compress')->dailyAt('02:00');

        // Génération des factures d'abonnement (1er jour du mois à 3h)
        $schedule->command('sicofil:generate-factures')->monthlyOn(1, '03:00');

        // Relance automatique des factures en retard (tous les jours à 9h)
        $schedule->command('sicofil:relance-factures')->dailyAt('09:00');
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
