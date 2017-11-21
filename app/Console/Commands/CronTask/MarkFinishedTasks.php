<?php

namespace App\Console\Commands\CronTask;

use App\App;
use App\Models\WorkDetail;
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
        // 获取到时、已完成的任务
        $app_rows = DB::table('apps')->where('is_brushing', '=', 1)
            ->where(function ($query) {
                $query->where('brush_num', '<=', 0)
                    ->orWhere('end_time', '<=', date('Y-m-d H:i:s'));
            })
            ->get();
        if ($app_rows->isEmpty()) {
            return true;
        }

        foreach ($app_rows as $app_row) {
            // * 统计各种情况，总数，按时间，按结果

            // 统计总刷数
            $brushed_num = WorkDetail::countBrushedNum($app_row->id);

            // 统计成功刷数
            $success_brushed_num = WorkDetail::getSuccessBrushedNum($app_row->id);

            // 计算失败数
            $fail_brushed_num = $brushed_num - $success_brushed_num;

            // * 标志已完成,完成时间
            $res = DB::table('task_keywords')->where([['app_id', '=', $app_row->id]])->update([
                'is_finish'           => 1,
                'real_end_time'       => date('Y-m-d H:i:s'),
                'brushed_num'         => $brushed_num, // 已刷数量
                'success_brushed_num' => $success_brushed_num, // 已刷数量
                'fail_brushed_num'    => $fail_brushed_num, // 已刷数量
                'remain_brush_num'    => $app_row->brush_num < 0 ? 0 : $app_row->brush_num, // 剩余未刷数量
            ]);

            // * 删除无效的刷记录
            // WorkDetail::delFailWork($app_row->id);

            // * 标志不在刷了
            $res = DB::table('apps')->where('id', $app_row->id)->update([
                'is_brushing' => 0,
            ]);

            // * 释放手机
            if ($app_row->mobile_group_id < 1000) {
                // 正式
                $res = DB::table('mobiles')->where([
                    ['mobile_group_id', '=', $app_row->mobile_group_id],
                ])->update([
                    'mobile_group_id' => 0,
                ]);

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
            } else {
                // 测试
                // 通知tianlin
                $msg = json_encode([
                    "应用"  => $app_row->app_name,
                    "关键词" => $app_row->keyword,
                ], JSON_UNESCAPED_UNICODE);
                $toMail = 'tianlin@xiaozi.com.cn';
                $cc     = ['297538600@qq.com', 'huangshimeng@xiaozi.com.cn'];
                Mail::raw($msg, function ($message) use ($toMail, $cc) {
                    $message->subject('jishua有应用打完了');
                    $message->to($toMail);
                    $message->cc($cc);
                });
            }
        }
    }
}
