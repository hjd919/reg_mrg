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
            // 判断没到结束时间并剩余量为0时，是否已经全部完成成功量
            if($app_row->brush_num <= 0 
            && strtotime($app_row->end_time)>time()){
                $valid_num = WorkDetail::countSuccessBrushNum($app_row->appid, $app_row->id, $app_row->start_time);

                $unsuccess_num = $app_row->success_num - $valid_num;
                if ($unsuccess_num > 0) {
                    DB::table('apps')->where('id', $app_row->id)->update(
                        ['brush_num' => $unsuccess_num]
                    );
                    continue;
                }
            }

            // * 统计各种情况，总数，按时间，按结果

            // 统计总刷数
            $brushed_num = WorkDetail::countBrushedNum($app_row->appid, $app_row->id);

            // 统计成功刷数
            $success_brushed_num = WorkDetail::getSuccessBrushedNum($app_row->appid, $app_row->id);

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

            // * 如果小于ios_apps的最小id，则标志最小account_id
            $app_min_account_id = DB::table('ios_apps')->where('appid', $app_row->appid)->value('min_account_id');
            $min_account_id     = WorkDetail::getMinAccountId($app_row->appid);
            if ($min_account_id < $app_min_account_id) {
                // 在刷旧账号，则更新该app的最小id
                DB::table('ios_apps')->where('appid', $app_row->appid)->update([
                    'min_account_id' => $min_account_id,
                ]);
            }

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

            }

            // 邮箱通知
            $msg = json_encode([
                "应用"  => $app_row->app_name,
                "关键词" => $app_row->keyword,
            ], JSON_UNESCAPED_UNICODE);
            // 按照用户表的email去通知
            $toMail = DB::table('users')->where('id', $app_row->user_id)->select('email')->value('email');
            $cc     = ['297538600@qq.com'];
            Mail::raw($msg, function ($message) use ($toMail, $cc) {
                $message->subject('jishua有应用打完了');
                $message->to($toMail);
                $message->cc($cc);
            });
        }
    }
}
