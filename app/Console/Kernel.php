<?php

namespace App\Console;

use App\Console\Commands\CronTask\CountUpHourlyTask;
use App\Console\Commands\CronTask\MakeUpAppBrushNum;
use App\Console\Commands\CronTask\MakeUpMobileNum;
use App\Console\Commands\CronTask\MarkFinishedTasks;
use App\Console\Commands\CronTask\MarkMobileValid;
use App\Console\Commands\CronTask\ResetAppleidState;
use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\Data\ToIosApp;
use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Import\ImportAppleids;
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
        ImportAppleids::class,
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
        // 定时重置未上报的邮箱
        ResetAppleidState::class,
        // 定时还原手机
        MarkMobileValid::class,
        // 定时还原手机
        CountUpHourlyTask::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 每分钟判断并补充手机成功量
        $schedule->command('make_up:app_brush_num')->cron('*/1 * * * * *')->withoutOverlapping();

        // 每3分钟判断并标示手机无效
        $schedule->command('make_up:mobile_num')->cron('*/3 * * * * *')->withoutOverlapping();

        // 每分钟判断并标示任务已完成
        $schedule->command('mark:finished_tasks')->cron('*/1 * * * * *')->withoutOverlapping();

        // 每3分钟判断并标示手机有效
        $schedule->command('mark:mobile_valid')->cron('*/3 * * * * *')->withoutOverlapping();

        // 每小时00分统计每小时的任务情况
        $schedule->command('count_up:hourly_task')->cron('0 */1 * * * *')->withoutOverlapping();

        // $schedule->command('reset:appleid_state')->cron('*/10 * * * * *');
    }
}
