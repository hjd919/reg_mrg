<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MakeupUsedAccountId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make_up:used_account_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $appid               = '1211055336';
        $used_account_id_key = "used_account_ids:appid_{$appid}";
        echo Redis::sSize($used_account_id_key);
        die;
        $work_detail = WorkDetail::getWorkDetailTable($appid)->select('account_id')->where('create_time', '>', '2017-12-15 16:00:00')->where('appid', $appid)->get();
        echo count($work_detail->toArray());
        foreach ($work_detail as $r) {
            $res = Redis::sAdd($used_account_id_key, $r->account_id);
            print_r($res) . "\n";
        }
    }
}
