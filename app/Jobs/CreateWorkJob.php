<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\WorkDetail;
use Illuminate\Support\Facades\DB;

class CreateWorkJob extends Job
{
    const TASK_SIZE = 3;
    protected $appid;
    protected $work_id;
    protected $email_rows;
    protected $device_rows;
    protected $app_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($work_id, $appid, $email_rows, $device_rows, $app_id)
    {
        $this->appid       = $appid;
        $this->work_id     = $work_id;
        $this->email_rows  = $email_rows;
        $this->device_rows = $device_rows;
        $this->app_id      = $app_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appid       = $this->appid;
        $work_id     = $this->work_id;
        $email_rows  = $this->email_rows;
        $device_rows = $this->device_rows;
        $app_id      = $this->app_id;

        // 插入work_detail
        $work_detail = [];
        foreach ($email_rows as $key => $email_row) {

            // 统计账号使用次数
            DB::table('emails')->where('id', $email_row->id)->increment('use_num');

            $work_detail[] = [
                'work_id'    => $work_id,
                'appid'      => $appid,
                'app_id'     => $app_id,
                'account_id' => $email_row->id,
                'device_id'  => $device_rows[$key]->id,
            ];
        }

        // 添加work_detail记录
        WorkDetail::add($appid, $work_detail);

        // 剩余数量减少3
        App::where('id', $app_id)->decrement('brush_num', self::TASK_SIZE);

    }
}
