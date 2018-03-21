<?php

namespace App\Console;

use App\Console\Commands\CronTask\CopyAppleids;
use App\Console\Commands\CronTask\ResetState;
use App\Console\Commands\Import\ImportAppleids;
use Illuminate\Console\Scheduling\Schedule;
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
        ResetState::class,
    ];

    /**
     * Define the application's command schedule.
     * 462144
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $filePath = './cron.log';
        $schedule->command('copy:appleids')->cron('59 */1 * * * *')->appendOutputTo($filePath);
        $schedule->command('reset:state')->cron('* */1 * * * *')->appendOutputTo($filePath);
    }
}
