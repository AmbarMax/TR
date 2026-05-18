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
         $schedule->command('app:discord-role-synchronization')->everyThirtyMinutes();
         // 2026-05-18: Disabled — host SFTP v-buf-04.sparkedhost.us is dead since April.
         // Was filling logs with connection errors every 30 min.
         // Re-enable when a new Discord badge source is implemented.
         // $schedule->command('app:discord-badge-synchronization')->everyThirtyMinutes();
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
