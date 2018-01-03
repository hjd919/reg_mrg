<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MakeupUsedAccountId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:appid_work_detail';

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
        // 重新分配appid到新的表
        $table_key = 2;
        $appid     = 1120180668;
        $new_table = 'work_detail' . $table_key;
        $old_table = 'work_detail';
        // $res       = DB::insert("insert into {$new_table} select * from {$old_table} where appid={$appid}");
        // $res1      = DB::delete("delete  FROM `work_detail` WHERE `appid` = {$appid} ");
        // $res2      = DB::update("UPDATE `ios_apps` SET `work_detail_table` = '{$table_key}' WHERE `ios_apps`.`appid` = {$appid}");
        $res3 = Redis::hSet('work_detail_table', $appid, $table_key);

        var_dump($res);
        var_dump($res1);
        var_dump($res2);
        var_dump($res3);
        die;

        // 增加work缓存
        Redis::hMSet('work_table_key', ['work_id' => 3286177, 'work_table' => 'works1']);
        $rpows = Redis::hMGet('work_table_key', ['work_id', 'work_table']);
        print_r($rpows);
        die;

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
