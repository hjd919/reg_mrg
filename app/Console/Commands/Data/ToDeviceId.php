<?php

namespace App\Console\Commands\Data;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ToDeviceId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to:device_id';

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
        die('work_detail gaile');
        // * to device_id
        $i = 0;
        while (1) {
            $rows = DB::table('work_detail')->select('id', 'udid')->where('device_id', 0)->limit(1000)->get();
            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                $device_id = DB::table('devices')->select('id')->where('udid', $row->udid)->value('id');
                $res       = DB::table('work_detail')->where('id', $row->id)->update(['device_id' => $device_id]);
                if (!$res) {
                    echo '更新失败';
                }
            }
            $i++;
            echo '执行' . $i . '次' . "\n";
        }
    }
}
