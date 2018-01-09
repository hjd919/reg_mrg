<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\WorkDetail;
use App\Support\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateWorkDetailJob extends Job
{
    protected $work_id;
    protected $account_id;
    protected $status;
    protected $fail_reason;
    protected $dama;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($work_id, $account_id, $status, $fail_reason, $dama)
    {
        $this->work_id     = $work_id;
        $this->account_id  = $account_id;
        $this->status      = $status;
        $this->fail_reason = $fail_reason;
        $this->dama        = $dama;
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

        if ($work_rows->app_id >= 5095 && $work_rows->app_id <= 5097) {
            $message = 'work_id:' . $this->work_id . 'app_id:' . $work_rows->app_id . "--dama:" . $this->dama;
            Util::log('UpdateWorkDetailJob', $message);
        }

        if ($this->dama) {
            // 统计打码次数
            App::where('id', $work_rows->app_id)->increment('dama', $this->dama);
        }

        // * 根据任务id和账号id更新刷任务记录状态
        WorkDetail::updateStatus($work_rows->appid, $this->work_id, $this->account_id, $this->status, $this->fail_reason);

    }
}
