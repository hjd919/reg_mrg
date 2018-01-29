<?php

namespace App\Console\Commands\Data;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DealData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deal:data';

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
        // 删除缓存和db中非13，14，15，0，的记录
        $appid  = 1325424608;
        $size   = 100;
        $offset = 0;
        $i      = 0;
        while (1) {
            $s    = 0;
            $rows = DB::table('work_detail11')->select('account_id')->groupBy('account_id')->where('appid', $appid)->whereNotIn('status', [0, 13, 14, 15])->limit($size)->offset($offset)->pluck('account_id');
            if (!$rows) {
                break;
            }
            foreach ($rows as $account_id) {
                DB::table('work_detail11')->where('account_id', $account_id)->where('appid', $appid)->delete();
                $res = Redis::sRemove('used_account_ids:appid_' . $appid, $account_id);
                if ($res) {
                    $s++;
                }

            }
            echo "offset-{$offset}:success-{$s}\n";

            $offset += $size;
        }
        die;
        // $appids = [
        //     843666882,
        //     1144417156,
        //     1310151835,
        //     1325424608,
        // ];
        // $size = 100;

        // foreach ($appids as $appid) {
        //     $table_key = Redis::hGet('work_detail_table', $appid);
        //     $table     = 'work_detail' . ($table_key ? $table_key : '');
        //     $offset    = 0;
        //     $s         = 0;
        //     while (1) {
        //         $wk_rows = DB::select("select account_id from `{$table}` where `appid` = {$appid} group by `account_id` limit $offset,$size");
        //         if (!$wk_rows) {
        //             break;
        //         }
        //         echo $wk_rows[0]->account_id . "\n";
        //         $offset += $size;
        //         foreach ($wk_rows as $wk_row) {
        //             $cache_num = Redis::sAdd('used_account_ids:appid_' . $appid, $wk_row->account_id);
        //             if ($cache_num) {
        //                 $s++;
        //             }
        //         }
        //     }
        //     echo "appid:{$appid}--success:{$s}\n";
        // }
        // die;
        // 统计最近一周app的已刷量
        $appids = DB::table('tasks')->select('appid')->where('created_at', '>', date('Y-m-d', strtotime('-2 weeks')))->groupBy('appid')->pluck('appid');
        foreach ($appids as $appid) {
            $table_key = Redis::hGet('work_detail_table', $appid);
            $table     = 'work_detail' . ($table_key ? $table_key : '');
            $db_num    = DB::select("select count(*) as db_num from (select count(account_id) as aggregate from `{$table}` where `appid` = {$appid} group by `account_id`) as a")[0]->db_num;

            $cache_num = Redis::sSize('used_account_ids:appid_' . $appid);

            echo "appid:{$appid}--db_num:{$db_num}--cache_num:{$cache_num}";
            if ($db_num <= $cache_num) {
                Redis::sAdd('account_policy_2', $appid);

                // 删除非账号失败的情况

                // echo "--match";
                // } elseif ($db_num < $cache_num) {
                //     echo "--缓存更多h";
                //     // $appids[] = $appid;
            } else {
                echo "--db更多";
            }
            echo "\n";
        }

        // 补充cache中的值

    }
}
