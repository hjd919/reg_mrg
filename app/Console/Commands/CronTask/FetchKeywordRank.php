<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;

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
            // 查询24小时前开始任务的app_ids
            $app_ids = App::select('id', 'keyword')->where('start_time', '<=', date('Y-m-d H', strtotime('-1 days')))->get();
            if ($app_ids->isEmpty()) {
                return true;
            }
            $app_ids = json_encode($app_ids);
        }

        // 获取关键词排名 {[id,after_rank,on_rank_time,on_rank_start,on_rank_end]}
        exec("casperjs ./casperjs/chandashi.js --appid={$appid} --app_ids={$app_ids}", $result);
        Util::log('keyword_rank', $keyword_rank);

        // 处理结果
        if (empty($result[0]) || !($keyword_rank = json_decode($result[0]))) {

            // 控制发邮件频率，一分钟一个人只发一封
            $key = 'notify_fail_fetch_rank:email_' . $toMail;
            if (!Redis::get($key)) {
                Redis::set($key, 1);
                Redis::expire($key, 600);

                // 邮箱通知
                $msg = '获取关键词排名失败' . json_encode(compact('app_ids', 'appid'));

                $toMail = '297538600@qq.com';
                Mail::raw($msg, function ($message) use ($toMail) {
                    $message->subject('获取关键词排名失败');
                    $message->to($toMail);
                });
            }
        }

        // 保存结果
        foreach ($keyword_rank as $d) {
            $id = $d['id'];
            unset($d['id']);
            App::where('id', $id)->update($d);
        }
    }
}
