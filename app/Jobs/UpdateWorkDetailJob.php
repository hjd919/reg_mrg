<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\WorkDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateWorkDetailJob extends Job
{
    protected $work_id;
    protected $account_id;
    protected $status;
    protected $fail_reason;
    protected $dama;
    protected $comment_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($work_id, $account_id, $status, $fail_reason, $dama, $comment_id)
    {
        $this->work_id     = $work_id;
        $this->account_id  = $account_id;
        $this->status      = $status;
        $this->fail_reason = $fail_reason;
        $this->dama        = $dama;
        $this->comment_id  = $comment_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Util::log('UspdateWorkDetailJob', json_encode([$this->work_id, $this->account_id, $this->status, $this->fail_reason]));

        // 根据work_id查询appid
        $work_table = Redis::get('work_table');
        $work_rows  = DB::table($work_table)->select('appid', 'app_id')->where('id', $this->work_id)->first();
        if (!$work_rows) {
            return true;
        }

        $appid  = $work_rows->appid;
        if(!$appid){
            return true;
        }
        $app_id = $work_rows->app_id;
        $status = $this->status;

        $dama = $this->dama;
        if ($dama) {
            // 统计打码次数
            App::where('id', $app_id)->increment('dama', $dama);

            DB::table('dama')->insert([
                'appid'  => $appid,
                'app_id' => $app_id,
                'dama'   => $dama,
            ]);
        }

        $comment_id = $this->comment_id;
        $account_id = $this->account_id;

        // 错误原因
        $fail_reason = $this->fail_reason;
        if ($status != 3) {
            // 失败

            // 标志评论未获取
            if ($comment_id) {
                $tmp = Redis::sAdd('useful_comment_ids:appid_' . $appid, $comment_id);
            }

            // 处理策略2
            if (Redis::sIsMember('account_policy_2', $appid) && !in_array($fail_reason, [13, 14, 15])) {
                // 删除无效账号外的记录 13，14，15
                // WorkDetail::deleteInvalid($appid, $account_id);

                // 缓存中增加可用账号
                // Redis::sAdd("useful_account_ids:appid_{$appid}", $account_id);
            }
        } else {
            // 成功

            // 标志评论已获取
            if ($comment_id) {
                $tmp = Redis::sAdd('used_comment_ids:appid_' . $appid, $comment_id);
            }

            // 缓存中增加已使用账号
            Redis::sAdd("used_account_ids:appid_{$appid}", $account_id);
        }

        // * 根据任务id和账号id更新刷任务记录状态
        WorkDetail::updateStatus($appid, $this->work_id, $account_id, $status, $fail_reason);

    }
}
