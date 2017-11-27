<?php

namespace App\Console\Commands\CronTask;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CountUpHourlyTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count_up:hourly_task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now_date = date('Y-m-d H:i:s');

        // 获取当前在跑的任务
        $rows = DB::table('apps')->where([
            ['start_time', '<=', $now_date],
            ['end_time', '>=', date('Y-m-d H:i:s', strtotime('-30 minutes'))],
        ])->get();
        if ($rows->isEmpty()) {
            // 获取不到，退出
            return true;
        }

        foreach ($rows as $app) {
            $hour_time = date('Y-m-d H', strtotime('-1 hours'));

            // 判断是否已经统计了
            $hourl_app_stat = DB::table('hourl_app_stat')->where([
                'app_id'    => $app->id,
                'hour_time' => $hour_time,
            ])->get();
            if (!$hourl_app_stat->isEmpty()) {
                continue;
            }

            // 统计上小时总刷数
            $brushed_num = WorkDetail::countBrushedNumLastHour($app_row->appid, $app_row->id, $hour_time);

            // 统计成功刷数
            $success_brushed_num = WorkDetail::countBrushedNumLastHour($app_row->appid, $app_row->id, $hour_time, ['status' => 3]);

            // 计算失败数
            $fail_brushed_num = $brushed_num - $success_brushed_num;

            // 记录统计app每小时的量
            DB::table('hourl_app_stat')->insert([
                'app_id'              => $app->id,
                'task_id'             => $app->task_id,
                'task_keyword_id'     => $app->task_keyword_id,
                'ios_app_id'          => $app->ios_app_id,
                'hour_time'           => $hour_time,
                'brushed_num'         => $brushed_num,
                'success_brushed_num' => $success_brushed_num,
                'fail_brushed_num'    => $fail_brushed_num,
            ]);
        }

    }
}
