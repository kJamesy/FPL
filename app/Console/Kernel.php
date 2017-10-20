<?php

namespace App\Console;

use App\Jobs\DispatchLeaguePlayersJob;
use App\Jobs\DispatchPlayerScoresJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('supervise:queue-worker')->everyMinute();
//        $schedule->job(new DispatchLeaguePlayersJob())->dailyAt('23:00');
//        $schedule->job(new DispatchPlayerScoresJob())->dailyAt('05:00');
        $schedule->job(new DispatchLeaguePlayersJob())->dailyAt('16:34');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
