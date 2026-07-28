<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('wa:reviewer-reminders')->dailyAt('08:00');
        $schedule->command('tenants:check-expiry')->dailyAt('07:00');
        $schedule->command('points:auto-sync')->everyFifteenMinutes()->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
