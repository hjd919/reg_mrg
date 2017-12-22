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
    protected $signature = 'fetch:keyword_rank';

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
        echo 'hello';
        pclose(popen("php ./artisan to:max_min_id", "r"));
    }
}
