<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    protected function schedule(Schedule $schedule): void {
        $schedule->command('transactions:mark-overdue')->dailyAt('00:05');
        $schedule->command('notify:due-tomorrow')->dailyAt('17:00');
        $schedule->command('notify:overdue')->dailyAt('09:00');
        $schedule->command('notify:monthly-summary')->monthlyOn(1, '17:00');
    }

    protected function commands(): void {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
