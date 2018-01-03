<?php

namespace App\Jobs;

use App\Models\WorkDetail;
use App\Support\Util;

class UpdateWorkDetailJob extends Job
{
    protected $work_id;
    protected $account_id;
    protected $status;
    protected $fail_reason;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($work_id, $account_id, $status, $fail_reason)
    {
        $this->work_id     = $work_id;
        $this->account_id  = $account_id;
        $this->status      = $status;
        $this->fail_reason = $fail_reason;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Util::log('UpdateWorkDetailJob', json_encode([$this->work_id, $this->account_id, $this->status, $this->fail_reason]));

        // * 根据任务id和账号id更新刷任务记录状态
        WorkDetail::updateStatus($this->work_id, $this->account_id, $this->status, $this->fail_reason);
    }
}
