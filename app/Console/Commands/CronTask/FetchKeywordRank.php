<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use App\Support\Util;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class FetchKeywordRank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:keyword_rank {--appid=0} {--app_ids=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取关键词排名';

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
        // pclose(popen("php ./artisan to:max_min_id", "r"));
        $appid   = $this->option('appid');
        $app_ids = $this->option('app_ids');

        if (!$appid) {
            echo 'que canshu';
            return true;
        }

        if (!$app_ids) {
            $start_time  = date('Y-m-d H', strtotime('-1 days'));

            // 查询24小时前开始任务的app_ids
            $app_ids = App::select('id', 'keyword')
                ->where('create_time', '>', date('Y-m-d', strtotime('-1 days')))
                ->where('create_time', '<', date('Y-m-d'))
                ->where('start_time', '<=', $start_time)
                ->where('after_rank', '=', 0)
                ->where('appid', $appid)
                ->get();
            if ($app_ids->isEmpty()) {
                return true;
            }
            $app_ids = json_encode($app_ids, JSON_UNESCAPED_UNICODE);
        }

        // 获取关键词排名 {[id,after_rank,on_rank_time,on_rank_start,on_rank_end]}
        $cmd = "casperjs --web-security=no --cookies-file=./casperjs/cookie.txt ./casperjs/chandashi.js --appid={$appid} --app_ids='{$app_ids}'";
        // Util::log('cmd', $cmd);
        exec($cmd, $result);
        // Util::log('result', $result);

        // 处理结果
        if (empty($result[0]) || !($keyword_rank = json_decode($result[0], true))) {
            // echo '获取关键词排名失败';
            // $toMail = '297538600@qq.com';
            
            // 控制发邮件频率，一分钟一个人只发一封
            // $key = 'notify_fail_fetch_rank:email_' . $toMail;
            // if (!Redis::get($key)) {
            //     Redis::set($key, 1);
            //     Redis::expire($key, 600);

            //     // 邮箱通知
            //     $msg = '获取关键词排名失败' . json_encode(compact('app_ids', 'appid'));

            //     Mail::raw($msg, function ($message) use ($toMail) {
            //         $message->subject('获取关键词排名失败');
            //         $message->to($toMail);
            //     });
            // }
            return true;
        }

        // 保存结果
        foreach ($keyword_rank as $d) {

            // 更新榜单记录
            $app_data = [
                'on_rank_start' => date('Y-m-d H:i:s', $d['on_rank_start']),
                'on_rank_end'   => date('Y-m-d H:i:s', $d['on_rank_end']),
                'after_rank'    => $d['after_rank'],
                'on_rank_time'  => $d['on_rank_time'],
            ];
            App::where('id', $d['id'])->update($app_data);

            // 记录每小时榜单记录
            DB::table('app_ranks')->where('app_id',$d['id'])->delete();
            $rank_data = $d['rank_data'];
            $app_ranks = [];
            foreach ($rank_data as $r_d) {
                $app_ranks[] = [
                    'app_id'  => $d['id'],
                    'appid'   => $appid,
                    'keyword' => $d['keyword'],
                    'rank'    => $r_d[1],
                    'time'    => date('Y-m-d H:i:s', $r_d[0] / 1000),
                ];
            }
            DB::table('app_ranks')->insert($app_ranks);
        }
    }
}
