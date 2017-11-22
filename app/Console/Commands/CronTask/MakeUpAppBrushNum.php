<?php

namespace App\Console\Commands\CronTask;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeUpAppBrushNum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make_up:app_brush_num';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '补充机刷数量';

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
        // 获取已刷完的任务
        $app_rows = DB::table('apps')->where([
            ['brush_num', '<=', 0],
            ['is_brushing', '=', 1],
        ])->get();
        if ($app_rows->isEmpty()) {
            // 获取不到，退出
            return true;
        }

        // 遍历已刷完任务，统计已刷成功量，计算出未成功量，如果有未成功量则更新补充
        foreach ($app_rows as $app_row) {

            // 统计这个app的有效量：app_id,app时间段,有效
            $valid_num = WorkDetail::countSuccessBrushNum($app_row->appid, $app_row->id, $app_row->start_time);

            $unsuccess_num = $app_row->success_num - $valid_num;
            if ($unsuccess_num > 0) {
                DB::table('apps')->where('id', $app_row->id)->update(
                    ['brush_num' => $unsuccess_num]
                );
            }
        }
    }
}
