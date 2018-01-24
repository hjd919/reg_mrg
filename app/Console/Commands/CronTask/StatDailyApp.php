<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatDailyApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stat:daily_app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日统计';

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
        // 每日app刷机情况统计
        // 获取work_detail昨天每个app的数量情况
        // 1. 获取最大表索引
        $max_table_id = DB::table('ios_apps')->max('work_detail_table');
        $yester_date  = date('Y-m-d', strtotime('-1 days'));
        $today_date   = date('Y-m-d');

        // 2. 遍历所有work_detail表
        for ($table_id = 0; $table_id <= $max_table_id; $table_id++) {
            $table_name = "work_detail" . ($table_id ?: '');

            // 3. 统计昨天每个app的数量 succss_num fail_num
            $stat_rows = DB::table($table_name)
                ->select(DB::raw('appid,status,count(*) as num'))
                ->where([
                    ['create_time', '>=', $yester_date],
                    ['create_time', '<', $today_date],
                    ['status', '!=', 1],
                ])
                ->groupBy('appid')
                ->groupBy('status')
                ->get();
            // 判断是否有数据
            if ($stat_rows->isEmpty()) {
                continue;
            }

            // 整理数据
            $succss_num = $fail_num = [];
            foreach ($stat_rows as $stat_row) {
                $appid = $stat_row->appid;

                if ($stat_row->status == 3) {
                    $succss_num[$appid] = $stat_row->num;
                } else {
                    $fail_num[$appid] = $stat_row->num;
                }
            }

            // 4. 统计昨天每个app的被封账号数 invalid_email_num
            $stat_rows = DB::table($table_name)
                ->select(DB::raw('appid,fail_reason,count(*) as num'))
                ->where([
                    ['create_time', '>=', $yester_date],
                    ['create_time', '<', $today_date],
                    ['fail_reason', '=', 14],
                ])
                ->groupBy('appid')
                ->get();

            // 整理数据
            $invalid_email_num = [];
            foreach ($stat_rows as $stat_row) {
                $invalid_email_num[$stat_row->appid] = $stat_row->num;
            }
            // 5. 添加每日app统计记录
            $data = [];
            foreach ($succss_num as $appid => $success) {

                // 6. 统计昨天每个appid的打码的数量 dama
                $dama = DB::table('dama')
                    ->where([
                        ['created_at', '>=', $yester_date],
                        ['created_at', '<', $today_date],
                        ['appid', '=', $appid],
                    ])
                    ->groupBy('appid')
                    ->count();

                // 查找user_id
                $user_id = App::where([
                    ['create_time', '>=', $yester_date],
                    ['create_time', '<', $today_date],
                    ['appid', '=', $appid],
                ])->value('user_id');

                $f_num       = isset($fail_num[$appid]) ? $fail_num[$appid] : 0;
                $i_email_num = isset($invalid_email_num[$appid]) ? $invalid_email_num[$appid] : 0;

                $data[] = [
                    'date'              => $yester_date,
                    'appid'             => $appid,
                    'user_id'           => $user_id,
                    'total_num'         => $success + $f_num,
                    'fail_num'          => $f_num,
                    'success_num'       => $success,
                    'invalid_email_num' => $i_email_num,
                    'dama'              => $dama,
                ];
            }

            $insert_num = DB::table('daily_app_stat')->insert($data);
            echo "{$insert_num}--{$table_name}\n";
        }
    }
}
