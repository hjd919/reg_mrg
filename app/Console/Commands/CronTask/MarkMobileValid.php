<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MarkMobileValid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mark:mobile_valid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '标志手机正常';

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
        $now_time = time();
        // 获取到时、已完成的任务
        $rows = DB::table('mobiles')->whereIn('is_normal', [0, 2])->get();
        if ($rows->isEmpty()) {
            return true;
        }

        foreach ($rows as $mobile) {
            // * 判断是否有效
            $mobile_access_time = Redis::hGet('mobiles_access_time', $mobile->device_id);
            $diff_time          = $now_time - $mobile_access_time;
            if ($diff_time <= 600) {
                // * 正常请求了，设置回有效
                DB::table('mobiles')->where('id', $mobile->id)->update(['is_normal' => 1]);

                $mobile_group_id = $mobile->mobile_group_id;
                $app             = App::where('mobile_group_id', $mobile_group_id)->select('id')->first();
                if ($app) {
                    // 手机异常数减少
                    App::where('id', $app->id)->decrement('fail_mobile_num');
                    
                    // 标志已恢复
                    // DB::table('error_mobile_log')->where('mobile_id',$mobile->id)->update(['is_recover'=>1]);
                }
            }

            // 超过2个小时，还没请求，标志为2 邮件通知
            // if ($diff_time > 7200) {

                /*if ($mobile->is_normal == 0) {

                // 邮箱通知
                $msg = 'jishua-有超2小时不请求的异常手机了';
                // 按照用户表的email去通知
                $toMail = '297538600@qq.com';
                $cc     = DB::table('users')->whereIn('id', [9, 11, 10, 12])->select('email')->pluck('email')->toArray();
                Mail::raw($msg, function ($message) use ($toMail, $cc) {
                $message->subject('jishua-有超2小时不请求的异常手机了');
                $message->to($toMail);
                $message->cc($cc);
                });
                }*/

                // 标示为2状态
                // DB::table('mobiles')->where('id', $mobile->id)->update(['is_normal' => 2]);
            // }
        }
    }
}
