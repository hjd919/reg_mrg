<?php

namespace App\Console\Commands\Data;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ToDeviceId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:redis';

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
        // 更新假设备机器
        for ($i = 1010; $i <= 1013; $i++) {
            $res = DB::table('mobiles')->where('mobile_group_id', 0)->where('is_normal', 1)->limit(2)->update(['mobile_group_id' => $i]);
            if ($res) {
                echo $i . "\n";
            }
        }
        die;
        // 迁移redis数据
        $redis  = Redis::connection();
        $redis2 = Redis::connection('test');

        $ios_apps = DB::table('ios_apps')->get();
        // $key1     = "is_new_email:appid_{$appid}";
        // $key2     = 'last_email_id:appid_' . $appid;
        // $key3     = 'last_device_id:appid_' . $appid;
        foreach ($ios_apps as $ios_app) {
            $appid = $ios_app->appid;
            $key1  = "is_new_email:appid_{$appid}";
            $key2  = 'last_email_id:appid_' . $appid;
            $key3  = 'last_device_id:appid_' . $appid;

            if (!$redis->get($key2)) {
                continue;
            }
            $redis2->set($key1, $redis->get($key1));
            $redis2->set($key2, $redis->get($key2));
            $redis2->set($key3, $redis->get($key3));

            $val  = $redis2->get($key1);
            $val2 = $redis2->get($key2);
            $val3 = $redis2->get($key3);

            echo json_encode(compact('appid', 'val', 'val2', 'val3')) . "\n";
        }

        $work_detail_table = $redis->hGetAll('work_detail_table');
        print_r($work_detail_table);
        $redis2->hMSet('work_detail_table', $work_detail_table);
        $work_detail_table = $redis2->hGetAll('work_detail_table');
        print_r($work_detail_table);
        die;
        // // * to device_id
        // $i = 0;
        // while (1) {
        //     $rows = DB::table('work_detail')->select('id', 'udid')->where('device_id', 0)->limit(1000)->get();
        //     if ($rows->isEmpty()) {
        //         break;
        //     }

        //     foreach ($rows as $row) {
        //         $device_id = DB::table('devices')->select('id')->where('udid', $row->udid)->value('id');
        //         $res       = DB::table('work_detail')->where('id', $row->id)->update(['device_id' => $device_id]);
        //         if (!$res) {
        //             echo '更新失败';
        //         }
        //     }
        //     $i++;
        //     echo '执行' . $i . '次' . "\n";
        // }
    }
}
