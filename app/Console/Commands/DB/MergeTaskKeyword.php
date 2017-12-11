<?php

namespace App\Console\Commands\DB;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeTaskKeyword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge:task_keyword';

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
        $rows = DB::table('task_keywords')->get();
        foreach ($rows as $task_keyword) {
            DB::table('apps')->where('id', $task_keyword->app_id)->update([
                'real_end_time'       => $task_keyword->real_end_time,
                'brushed_num'         => $task_keyword->brushed_num, // 已刷数量
                'success_brushed_num' => $task_keyword->success_brushed_num, // 已刷数量
                'fail_brushed_num'    => $task_keyword->fail_brushed_num, // 已刷数量
                'remain_brush_num'    => $task_keyword->remain_brush_num, // 剩余未刷数量
                'hot'                 => $task_keyword->hot,
                'before_rank'         => $task_keyword->before_rank,
            ]);
        }
    }
}
