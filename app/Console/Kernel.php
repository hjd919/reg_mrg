<?php

namespace App\Console;

use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Import\ImportDevices;
use App\Console\Commands\Import\ImportEmails;
use App\Console\Commands\sendMailCommand;
use App\Console\Commands\Task\TaskMobileFail;
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
        ImportDevices::class,
        ImportEmails::class,
        MobileAdd::class,
        sendMailCommand::class,
        TaskMobileFail::class,
        ToDeviceId::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('task:mobile-fail')->cron('*/2 * * * * *')->appendOutputTo('./task_mobile_fail.txt');
    }
}
