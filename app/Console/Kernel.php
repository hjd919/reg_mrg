<?php

namespace App\Console;

use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Data\ToIosApp;
use App\Console\Commands\Data\JiaDevice;
use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\sendMailCommand;
use App\Console\Commands\Data\ToMaxMinId;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\Check\isNoAppleids;
use App\Console\Commands\Check\hasNewEmails;
use App\Console\Commands\DB\MergeTaskKeyword;
use App\Console\Commands\Import\ImportEmails;
use App\Console\Commands\Import\ImportDevices;
use App\Console\Commands\Import\ImportAppleids;
use App\Console\Commands\CronTask\sAddAppleids;
use App\Console\Commands\CronTask\CopyAppleids;
use App\Console\Commands\CronTask\MarkMobileValid;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CronTask\MakeUpMobileNum;
use App\Console\Commands\Data\MakeupUsedAccountId;
use App\Console\Commands\CronTask\MakeUpAppBrushNum;
use App\Console\Commands\CronTask\MarkFinishedTasks;
use App\Console\Commands\CronTask\CountUpHourlyTask;
use App\Console\Commands\CronTask\ResetAppleidState;

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
        ToMaxMinId::class,
        JiaDevice::class,
        MakeupUsedAccountId::class,
        sAddAppleids::class,
        MergeTaskKeyword::class,
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
        // 复制成功的账号
        CopyAppleids::class,
        isNoAppleids::class,
        hasNewEmails::class,
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
        $schedule->command('mark:finished_tasks')->cron('*/2 * * * * *')->withoutOverlapping();

        // 每3分钟判断并标示手机有效
        $schedule->command('mark:mobile_valid')->cron('*/3 * * * * *')->withoutOverlapping();

        // 每小时00分统计每小时的任务情况
        $schedule->command('count_up:hourly_task')->cron('0 */1 * * * *')->withoutOverlapping();

        // 账号状态重置
        $schedule->command('reset:appleid_state')->cron('*/2 * * * * *');

        // 复制成功账号
        $schedule->command('copy:appleids')->cron('*/30 * * * * *');

        // 添加有效账号
        $schedule->command('sAdd:appleids')->cron('0 */30 * * * *');
        
        // 判断是否需要添加邮箱
        //$schedule->command('check:is_no_appleids')->cron('0 */1 * * * *');

        // 判断是否有新邮箱跑
        // $schedule->command('check:has_new_emails')->cron('*/5 * * * * *');
    }
}
