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

//                 $table_sql2 = <<<EOF
        // CREATE TRIGGER `t_work_detail{$work_detail_table}_decr_num` AFTER INSERT ON `work_detail{$work_detail_table}` FOR EACH ROW update apps set brush_num=brush_num-1 where id=new.app_id
        // EOF;
        DB::transaction(function () {
            // 重新分配appid到新的表
            $table_key     = 11;
            $old_table_key = 5;
            $appid         = 1325424608;
            $old_table     = 'work_detail' . $old_table_key;

            $table_sql1 = <<<EOF
CREATE TABLE `work_detail{$table_key}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1进行中 2失败 3成功 4老数据',
  `fail_reason` TINYINT(1) NOT NULL DEFAULT '0',
  `work_id` int(11) NOT NULL,
  `appid` bigint(20) NOT NULL,
  `app_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_id_2` (`work_id`,`account_id`),
  KEY `appid_email` (`appid`,`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;
            // $res0 = DB::statement($table_sql1);

            $new_table = 'work_detail' . $table_key;
            // $res       = DB::insert("insert into {$new_table} select * from {$old_table} where appid={$appid}");
            $res1 = DB::delete("delete  FROM {$old_table} WHERE `appid` = {$appid} ");
            $res2 = DB::update("UPDATE `ios_apps` SET `work_detail_table` = '{$table_key}' WHERE `appid` = {$appid}");
            $res3 = Redis::hSet('work_detail_table', $appid, $table_key);

            // var_dump($res0) . "\n";
            // var_dump($res) . "\n";
            var_dump($res1) . "\n";
            var_dump($res2) . "\n";
            var_dump($res3) . "\n";

        }, 1);

        die;

        // 增加work缓存
        Redis::set('work_id', 3289142);
        Redis::set('work_table', 'works1');
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
