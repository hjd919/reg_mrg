<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class MakeUpMobileNum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make_up:mobile_num';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '补充手机数量';

    protected $now_time;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->now_time = time();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // * 判断是否有正在跑任务，没有，则退出
        $where = [
            ['brush_num', '>', 0],
            ['is_brushing', '=', 1],
        ];
        $doing_task = DB::table('apps')->where($where)->first();
        if (!$doing_task) {
            die('no doing task');
        }

        // * 获取手机，并循环
        $mobiles = DB::table('mobiles')->where('mobile_group_id', '>', 0)
            ->where('mobile_group_id', '<', 1000)
            ->get();

        try {
            foreach ($mobiles as $key => $mobile) {
                // 处理无效手机
                $this->handleInvalidMobile($mobile);

                echo "处理第" . ($key++) . "条mobiles\n";
            }

            // 判断当前正在跑任务异常手机是否多于2台
            $fail_mobile_total = App::where('is_brushing', 1)->sum('fail_mobile_num');
            if ($fail_mobile_total > 2) {
                $key     = 'is_send_error_mobile';
                $is_send = Redis::get($key);
                if ($is_send) {
                    return true;
                }
                Redis::set($key, 1);
                Redis::expire($key, 600);

                $apps = App::select('id', 'appid', 'app_name', 'keyword', 'fail_mobile_num')
                    ->where('is_brushing', 1)
                    ->where('fail_mobile_num', '>', 0)
                    ->get();

                $toMail = 'caoliang@xiaozi.com.cn';
                $cc     = [
                    '297538600@qq.com',
                    'huangshimeng@xiaozi.com.cn',
                    'tianlin@xiaozi.com.cn',
                    'tianshaokun@xiaozi.com.cn',
                    // 'su@xiaozi.com.cn',
                    'xizaihui@xiaozi.com.cn',
                ];
                $msg = "异常任务如下：\n" . json_encode($apps);
                Mail::raw($msg, function ($message) use ($toMail, $cc) {
                    $message->subject("在跑任务有异常手机{$fail_mobile_total}台");
                    $message->to($toMail);
                    $message->cc($cc);
                });
            }

        } catch (\Exception $e) {
            // $msg = $e->getMessage();

            // 邮件警告异常,一小时发一条

        }
    }

    // 处理无效手机
    public function handleInvalidMobile($mobile)
    {
        // * 获取手机最后访问时间（一定时间内，没有访问服务器则视为失效数）
        $mobile_access_time = Redis::hGet('mobiles_access_time', $mobile->device_id);

        // * 如果超过一定时间（10分钟），失效的手机则获取mobile_group_id=0的手机并更新为对应的mobile_group_id
        if ($this->now_time - $mobile_access_time > 600) {
            // echo '有手机异常' . json_encode([
            //     '$mobile_group_id'    => $mobile->mobile_group_id,
            //     '$device_id'          => $mobile->device_id,
            //     '$now_time'           => $this->now_time,
            //     '$mobile_access_time' => $mobile_access_time,
            // ]) . "\n";

            // 处理任务中的手机
            $mobile_group_id = $mobile->mobile_group_id;
            // 查询任务id
            $app = App::where('mobile_group_id', $mobile_group_id)->first();
            if ($app) {

                // 记录错误日志 error_mobile_log
                DB::table('error_mobile_log')->insert([
                    'appid'           => $app->appid,
                    'app_id'          => $app->id,
                    'mobile_group_id' => $mobile_group_id,
                    'mobile_id'       => $mobile->id,
                ]);

                // 手机异常数增加 App
                App::where('id', $app->id)->increment('fail_mobile_num');
            }

            // 标志为不正常手机
            DB::table('mobiles')->where('id', $mobile->id)->increment('error_num', 1, ['is_normal' => 0]);

            $app = DB::table('apps')->where('mobile_group_id', $mobile->mobile_group_id)->select('keyword', 'task_keyword_id')->first();

            // // * 获取mobile_group_id=0的手机，如果没有了则退出循环，并邮件警告
            // $mgi0 = DB::table('mobiles')->select('id')->where('mobile_group_id', 0)->first();
            // if (!$mgi0) {
            //     echo '没有mobile_group_id=0的手机' . json_encode([
            //         '$mobile_group_id' => $mobile->mobile_group_id,
            //         '$device_id'       => $mobile->device_id,
            //         '$mgi0'            => $mgi0,
            //     ]) . "\n";
            //     throw new \Exception('devices表中没有mobile_group_id=0的手机可以分配了，异常手机:' . $mobile->device_id . '|' .
            //         json_encode([
            //             'keyword'         => $app->keyword,
            //             'mobile_group_id' => $mobile->mobile_group_id,
            //             'mobile_id'       => $mobile->id,
            //         ], JSON_UNESCAPED_UNICODE));
            // }

            // * 统计异常手机数量

            // // 更新新的
            // $res = DB::table('mobiles')->where('id', $mgi0->id)->update(['mobile_group_id' => $mobile->mobile_group_id]);
            // if (!$res) {
            //     throw new \Exception('update mobile mobile_group_id error|update error');
            // }
        }

        return true;
    }
}
