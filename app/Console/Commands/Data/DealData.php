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
        $appid  = 1318070822;
	$work_table = '12';
        $size   = 1000;
        $offset = 0;
        $i      = 0;
        while (1) {
            $s    = 0;
            $rows = DB::table('work_detail12')->select('account_id')->groupBy('account_id')->where('appid', $appid)->whereNotIn('fail_reason', [0, 13, 14, 15])->limit($size)->pluck('account_id');
            if ($rows->isEmpty()) {
                break;
            }
            foreach ($rows as $account_id) {
                $res1 = DB::table('work_detail12')->where('account_id', $account_id)->where('appid', $appid)->delete();
                $res  = Redis::sRemove('used_account_ids:appid_' . $appid, $account_id);
                if ($res && $res1) {
                    $s++;
                }
                //file_put_contents($appid.'_delete_account_id.txt', $account_id . "\n", FILE_APPEND);
            }
            echo "offset-{$offset}:success-{$s}\n";

            $offset += $size;
        }
        die;
        $appid      = 1337550793;
        $useful_key = 'useful_comment_ids:appid_' . $appid;
        $used_key   = 'used_comment_ids:appid_' . $appid;

        // 删除未评论的
        $comment_ids = DB::table('comments')->select('id')->where('appid', $appid)->where('created_at', '>', '2018-01-29')->pluck('id');
        // die;

        // // 删除已刷的评论
        // // 获取已刷评论，判断是否有相同的内容

        // $comment_ids = Redis::sMembers('used_comment_ids:appid_' . $appid);
        $s = 0;
        foreach ($comment_ids as $comment_id) {
            $used_content = DB::table('comments')->select('content')->where('id', $comment_id)->value('content');
            $ids          = DB::table('comments')->select('id')->where('content', $used_content)->where('id', '!=', $comment_id)->pluck('id');
            if ($ids->isEmpty()) {
                continue;
            }

            $res = DB::table('comments')->whereIn('id', $ids)->delete();

            foreach ($ids as $id) {
                $res = Redis::sRemove($useful_key, $id);
                if ($res) {
                    $s++;
                }
            }
            $size = Redis::sSize($useful_key);

            echo "comment_id:$comment_id--还剩下{$size}--删除结果:" . var_export($res, true) . "\n";
        }

        // 如果有，则删除内容id
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
