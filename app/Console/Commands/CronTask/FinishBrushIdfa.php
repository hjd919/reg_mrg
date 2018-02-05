<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinishBrushIdfa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finish:brush_idfa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '标志任务完成';

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
        // 查询已经结束的任务
        $now_date              = date('Y-m-d H:i:s');
        $mobile_assign_actives = DB::table('mobile_assign_active')->where([
            ['end_time', '<', $now_date],
        ])->get();
        if (!$mobile_assign_actives->isEmpty()) {
            $this->finishActiveTask($mobile_assign_actives);
        }

        $now_date             = date('Y-m-d H:i:s');
        $mobile_assign_cilius = DB::table('mobile_assign_ciliu')->where([
            ['end_time', '<', $now_date],
        ])->get();
        if (!$mobile_assign_cilius->isEmpty()) {
            $this->finishCiliuTask($mobile_assign_cilius);
        }
        return true;
    }

    public function finishActiveTask($rows)
    {
        foreach ($rows as $row) {
            $task_id = $row->active_task_id;
            $id = $row->id;

            // 标志已完成
            DB::table('brush_idfas_active')->where('id', $task_id)->update([
                'is_finish' => 1,
            ]);
            DB::table('mobile_assign_active')->where('id', $id)->delete();
        }
    }

    public function finishCiliuTask($rows)
    {
        foreach ($rows as $row) {
            $task_id = $row->ciliu_task_id;
            $id = $row->id;

            // 标志已完成
            DB::table('brush_idfas_ciliu')->where('id', $task_id)->update([
                'is_finish' => 1,
            ]);
            DB::table('mobile_assign_ciliu')->where('id', $id)->delete();
        }
    }
}
