<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use App\Models\WorkDetail;
use App\Support\Util;
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
    protected $hour_time;
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
        $this->hour_time = date('Y-m-d H:00:00', strtotime('-1 hours'));

        // * 获取一小时前的work_detail表
        $max_table = DB::table('ios_apps')->max('work_detail_table');
        for ($i = 0; $i <= $max_table; $i++) {
            // 获取work_detail表
            $key               = $i ? $i : '';
            $work_detail_table = "work_detail" . $key;

            $this->handleCountTable($work_detail_table);
        }
        die;
        $now_date = date('Y-m-d H:i:s', strtotime('-59 minutes'));
        // 获取当前在跑的任务
        $rows = DB::table('apps')->where([
            ['create_time', '>', date('Y-m-d', strtotime('-1 days'))],
            ['start_time', '<=', $now_date],
            ['end_time', '>=', $now_date],
        ])->get();
        if ($rows->isEmpty()) {
            // 获取不到，退出
            return true;
        }
        DB::listen(function ($query) {
            Util::log('获取当前在跑的任务sql', $query->sql . var_export($query->bindings, true));
        });
        Util::log('获取当前在跑的任务app_rows', $rows);

        $hour_time = date('Y-m-d H', strtotime('-1 hours'));
        foreach ($rows as $app) {

            // 判断是否已经统计了
            $hourl_app_stat = DB::table('hourl_app_stat')->where([
                'app_id'    => $app->id,
                'hour_time' => $hour_time . ':00:00',
            ])->first();
            if ($hourl_app_stat) {
                continue;
            }

            // 统计上小时总刷数
            $brushed_num = WorkDetail::countBrushedNumLastHour($app->appid, $app->id, $hour_time);

            // 统计成功刷数
            $success_brushed_num = WorkDetail::countBrushedNumLastHour($app->appid, $app->id, $hour_time, ['status' => 3]);

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

    // * 获取一小时前的work_detail表
    public function handleCountTable($table)
    {
        $hour_time = $this->hour_time;
        // total_count
        $total_count_rows = DB::table($table)
            ->select(DB::raw('count(app_id) as app_id_count,app_id,appid'))
            ->where([
                ['create_time', '>=', $hour_time],
                ['create_time', '<=', date('Y-m-d H', strtotime('+1 hours', strtotime($hour_time)))],
            ])->groupBy('app_id')->get();
	if($total_count_rows->isEmpty()){
		echo "meiyou total_count_rows\n";
		return true;
	}

        // DB::listen(function ($query) {
        //     $sql      = $query->sql;
        //     $bindings = $query->bindings;
        //     foreach ($bindings as $replace) {
        //         $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
        //         $sql   = preg_replace('/\?/', $value, $sql, 1);
        //     }
        //     dd($sql);
        // });

        // valid_count
        $valid_count_rows = DB::table($table)
            ->select(DB::raw('count(app_id) as app_id_count,app_id'))
            ->where([
                ['create_time', '>=', $hour_time],
                ['create_time', '<=', date('Y-m-d H', strtotime('+1 hours', strtotime($hour_time)))],
                ['status', '=', 3],
            ])->groupBy('app_id')->get();
	if($valid_count_rows->isEmpty()){
		echo "meiyou total_count_rows\n";
		return true;
	}

        // 获取valid_rows app_id=>valid_count
        $app_id_valid_count = [];
        foreach ($valid_count_rows as $valid_count_row) {
            $app_id_valid_count[$valid_count_row->app_id] = $valid_count_row->app_id_count;
        }

        // 整理数据并入库
        foreach ($total_count_rows as $total_count_row) {
            $brushed_num         = $total_count_row->app_id_count;
            $success_brushed_num = isset($app_id_valid_count[$total_count_row->app_id])
            ? $app_id_valid_count[$total_count_row->app_id]
            : 0;

            $user_id = App::where('id',$total_count_row->app_id)->value('user_id');

            DB::table('hourl_app_stat')->insert([
                'app_id'              => $total_count_row->app_id,
                'user_id'             => $user_id,
                'appid'               => $total_count_row->appid,
                'hour_time'           => $hour_time,
                'brushed_num'         => $brushed_num,
                'success_brushed_num' => $success_brushed_num,
                'fail_brushed_num'    => $brushed_num - $success_brushed_num,
            ]);

            // 累加
            App::where('id', $total_count_row->app_id)->increment('hour_success_num', $success_brushed_num);
        }
    }
}
