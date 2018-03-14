<?php

namespace App\Console;

use App\Console\Commands\CronTask\CopyAppleids;
use App\Console\Commands\Import\ImportAppleids;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ImportAppleids::class,
        CopyAppleids::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    // protected function schedule(Schedule $schedule)
    // {
    //    //$schedule->command('reset:appleid_state')->cron('*/2 * * * * *');

    // }
}
