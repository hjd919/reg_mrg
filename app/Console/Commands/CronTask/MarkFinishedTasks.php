<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

            // * 标志已完成,完成时间
            $res = DB::table('task_keywords')->where([['app_id', '=', $app_row->id]])->update([
                'is_finish'     => 1,
                'real_end_time' => date('Y-m-d H:i:s'),
            ]);

            // * 标志不在刷了
            $res = DB::table('apps')->where('id', $app_row->id)->update([
                'is_brushing' => 0,
            ]);

            // * 释放手机
            if ($app_row->mobile_group_id < 1000) {
                $res = DB::table('mobiles')->where([
                    ['mobile_group_id', '=', $app_row->mobile_group_id],
                ])->update([
                    'mobile_group_id' => 0,
                ]);
            }

            // * 统计各种情况，总数，按时间，按结果

            // 通知曹亮
            $msg = json_encode([
                "应用"  => $app_row->app_name,
                "关键词" => $app_row->keyword,
            ], JSON_UNESCAPED_UNICODE);
            $toMail = 'caoliang@xiaozi.com.cn';
            $cc     = ['297538600@qq.com'];
            Mail::raw($msg, function ($message) use ($toMail, $cc) {
                $message->subject('jishua有应用打完了');
                $message->to($toMail);
                $message->cc($cc);
            });
        }
    }
}
