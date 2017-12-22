<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use Illuminate\Console\Command;

class CrondFetchKeywordRank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crond_fetch:keyword_rank';

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

        $start_time = date('Y-m-d H', strtotime('-1 days'));

        // 查询24小时前开始任务的app_ids
        $appids = App::select('appid')
            ->where('create_time', '>', date('Y-m-d', strtotime('-1 days')))
            ->where('create_time', '<', date('Y-m-d'))
            ->where('start_time', '<=', $start_time)
            ->where('after_rank', '=', 0)
            ->groupBy('appid')
            ->pluck('appid');
        if (!$appids) {
            return true;
        }

        // 抓取每个app的关键词
        foreach ($appids as $appid) {
            $this->call('fetch:keyword_rank', ['--appid' => $appid]);
        }
    }
}
