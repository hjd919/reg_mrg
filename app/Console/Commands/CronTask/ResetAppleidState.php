<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetAppleidState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:appleid_state';

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
        // 获取超时未任务
        $app_rows = DB::table('apps')->where([
            ['state', '=', 3],
            ['updated_at', '<', date('Y-m-d H:i:s', strtotime('-50 minutes'))],
        ])->get();
        if ($app_rows->isEmpty()) {
            // 获取不到，退出
            return true;
        }

        // reset回状态
        foreach ($app_rows as $row) {
            DB::table('apps')->where('id', $row->id)->update(['strAn3' => '好啊了', 'state' => 0]);
        }

    }
}
