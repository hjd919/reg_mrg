<?php

namespace App\Console\Commands\CronTask;

use App\App;
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

        // 2. 遍历所有work_detail表
        for ($table_id = 0; $table_id <= $max_table_id; $table_id++) {
            $table_name = "work_detail" . ($table_id ?: '');

            // 3. 统计昨天每个app的数量
            $stat_rows = DB::table($table_name)
                ->select(DB::raw('appid,status,count(*) as num'))
                ->where([
                    ['create_time', '>=', $yester_date],
                    ['status', '!=', 1],
                ])
                ->groupBy('appid')
                ->groupBy('status')
                ->get();

            // 4. 遍历统计并整理数据
            $succss_num = $fail_num = [];
            foreach ($stat_rows as $stat_row) {
                $appid = $stat_row->appid;
         
                if ($stat_row->status == 3) {
                    $succss_num[$appid] = $stat_row->num;
                } else {
                    $fail_num[$appid] = $stat_row->num;
                }
            }

            // 5. 添加每日app统计记录
            foreach ($succss_num as $appid => $success) {
                $data[] = [
                    'date'        => $yester_date,
                    'appid'       => $appid,
                    'total_num'   => $success + $fail_num[$appid],
                    'fail_num'    => $fail_num[$appid],
                    'success_num' => $success,
                ];
            }

            $insert_num = DB::table('daily_app_stat')->insert($data);
            echo "{$insert_num}--{$table_name}\n";
        }
    }
}
