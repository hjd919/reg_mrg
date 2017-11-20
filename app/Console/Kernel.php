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
        // 定时补成功量
        makeUpAppBrushNum::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 定时补充未完成的量
        $schedule->command('make_up:app_brush_num')->cron('*/1 * * * * *');
        // 定时补充异常的手机量
        $schedule->command('make_up:mobile_num')->cron('*/1 * * * * *');
        // ->appendOutputTo('./test.txt');
    }
}
