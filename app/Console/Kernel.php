<?php

namespace App\Console;

use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\Data\ToIosApp;
use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Import\ImportDevices;
use App\Console\Commands\Import\ImportEmails;
use App\Console\Commands\sendMailCommand;
use App\Console\Commands\Task\CheckMobileFail;
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
        CheckMobileFail::class,
        ToDeviceId::class,
        ToIosApp::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('test:test', function () {
            echo '111' . date('Y-m-d');
        })->cron('*/1 * * * * *')->appendOutputTo('./test.txt');
    }
}
