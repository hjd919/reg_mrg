<?php

namespace App\Console;

use App\Console\Commands\Data\Test;
use App\Console\Commands\DB\MobileAdd;
use App\Console\Commands\Data\ToIosApp;
use App\Console\Commands\Data\DealData;
use App\Console\Commands\Data\JiaDevice;
use App\Console\Commands\Data\ToDeviceId;
use App\Console\Commands\sendMailCommand;
use App\Console\Commands\Data\ToMaxMinId;
use App\Console\Commands\Data\RevertEmail;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\Data\VerifyCapcha;
use App\Console\Commands\Check\isNoAppleids;
use App\Console\Commands\Check\hasNewEmails;
use App\Console\Commands\DB\MergeTaskKeyword;
use App\Console\Commands\Import\ImportEmails;
use App\Console\Commands\Import\ImportDevices;
use App\Console\Commands\Import\ImportAppleids;
use App\Console\Commands\Import\ImportComments;
use App\Console\Commands\CronTask\sAddAppleids;
use App\Console\Commands\CronTask\StatDailyApp;
use App\Console\Commands\CronTask\CopyAppleids;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\Data\BackupInvalidEmails;
use App\Console\Commands\CronTask\MakeUpMobileNum;
use App\Console\Commands\Data\MakeupUsedAccountId;
use App\Console\Commands\CronTask\MarkMobileValid;
use App\Console\Commands\CronTask\FetchKeywordRank;
use App\Console\Commands\CronTask\MarkFinishedTasks;
use App\Console\Commands\CronTask\ResetAppleidState;
use App\Console\Commands\CronTask\MakeUpAppBrushNum;
use App\Console\Commands\CronTask\CountUpHourlyTask;
use App\Console\Commands\CronTask\MakeUpIdfaBrushNum;
use App\Console\Commands\CronTask\CrondFetchKeywordRank;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DealData::class,
        ImportComments::class,
        RevertEmail::class,
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
        BackupInvalidEmails::class,
        MergeTaskKeyword::class,
        StatDailyApp::class,
        FetchKeywordRank::class,
        VerifyCapcha::class,
        // 定时检查是否需要抓取关键词排名
        CrondFetchKeywordRank::class,
        // 定时补成功量
        MakeUpAppBrushNum::class,
        // 定时补idfa成功量
        MakeUpIdfaBrushNum::class,
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

        // 每分钟判断并补充idfa成功量
        // $schedule->command('make_up:idfa_brush_num')->cron('*/5 * * * * *')->withoutOverlapping();

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
        $schedule->command('sAdd:appleids')->cron('*/30 * * * * *');

        // 统计每天的app情况
        $schedule->command('stat:daily_app')->cron('0 0 * * * *')->withoutOverlapping();

        // 抓取排名更新
        $schedule->command('crond_fetch:keyword_rank')->cron('0 */1 * * * *');

        // 判断是否需要添加邮箱
        //$schedule->command('check:is_no_appleids')->cron('0 */1 * * * *');

        // 判断是否有新邮箱跑
        // $schedule->command('check:has_new_emails')->cron('*/5 * * * * *');
    }
}
