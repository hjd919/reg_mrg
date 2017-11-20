<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;

class MarkFinishedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mark:finished_tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '标志任务完成';

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
        // 获取已完成的任务
        $app_rows = DB::table('apps')->where([
            ['is_brushing', '=', 1],
            ['brush_num', '<=', 0],
        ])->get();
        if ($app_rows->isEmpty()) {
            return true;
        }

        foreach ($app_rows as $app_row) {

            // * 标志已完成和完成时间
            $valid_num = DB::table('task_keywords')->where([['app_id', '=', $app_row->id]])->update([
                'is_finish'     => 1,
                'real_end_time' => date('Y-m-d H:i:s'),
            ]);

            // * 释放手机
            if ($app_row->mobile_group_id <= 1000) {
                $valid_num = DB::table('mobile')->where([
                    ['mobile_group_id', '=', $app_row->mobile_group_id],
                ])->update([
                    'mobile_group_id' => 0,
                ]);
            }

        }
    }
}
