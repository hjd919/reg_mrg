<?php

namespace App\Console;

use App\Console\Commands\CronTask\MakeUpAppBrushNum;
use App\Console\Commands\CronTask\MakeUpMobileNum;
use App\Console\Commands\CronTask\MarkFinishedTasks;
use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\Data\ToIosApp;
use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Import\ImportDevices;
use App\Console\Commands\Import\ImportEmails;
use App\Console\Commands\sendMailCommand;
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
        ToDeviceId::class,
        ToIosApp::class,
        // 定时补成功量
        MakeUpAppBrushNum::class,
        // 定时补手机量
        MakeUpMobileNum::class,
        // 标志任务完成
        MarkFinishedTasks::class,
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
//        $schedule->command('make_up:mobile_num')->cron('*/1 * * * * *');
        // ->appendOutputTo('./test.txt');
        $schedule->command('mark:finished_tasks')->cron('*/1 * * * * *');
    }
}
