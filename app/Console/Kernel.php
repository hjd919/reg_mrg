<?php

namespace App\Console;

use App\Console\Commands\CronTask\CopyAppleids;
use App\Console\Commands\CronTask\DeleteError;
use App\Console\Commands\CronTask\ResetState;
use App\Console\Commands\Import\ImportAppleids;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CpAppleidDevice;
use App\Console\Commands\RegMail;

//use App\Console\Commands\TestCommand;

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
        DeleteError::class,
        CpAppleidDevice::class,
        RegMail::class,
        //TestCommand::class,
    ];

    /**
     * Define the application's command schedule.
     * 462144
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $filePath = './cron.log';
        $schedule->command('copy:appleids')->cron('0 */1 * * * *');
        $schedule->command('reset:state')->cron('* */1 * * * *');
        //$schedule->command('delete:error')->cron('58 16 * * * *');
        //$schedule->command('copy:appleids_test')->cron('1 */1 * * * *')->appendOutputTo($filePath);
    }
}
