<?php

namespace App\Console\Commands\DB;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MobileAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobile:add';

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
        $rows = DB::table('mobiles')->get();
        foreach ($rows as $key => $row) {
            DB::table('mobiles')->where('id', $row->id)->update(['no' => $key + 1]);
        }
        die;
        // * 导入手机device_id到mobile表
        // 从works获取device_id
        $rows = DB::table('works')
            ->groupBy('device_id')
            ->select('device_id')
            ->get();

        $s = $r = 0;
        // 循环数据，添加到mobile表中
        foreach ($rows as $key => $row) {
            if (strlen($row->device_id) < 10) {
                $r++;
                continue;
            }
            $device_id = $row->device_id;
            DB::table('mobile')->insert([
                'device_id'       => $device_id,
                'alias'           => '编号' . $key,
                'mobile_group_id' => 1,
            ]);
        }
        // 添加到mobile表中
        // $file = 'devices' . date('md') . '.csv';
        // $fp   = fopen($file, 'r');
        // $r    = $i    = $j    = 0;
        // while (($data = fgetcsv($fp)) !== false) {
        //     list($SerialNumber, $IMEI, $Bluetooth, $WIFI, $UDID) = $data;
        //     if (empty($SerialNumber) || empty($IMEI) || empty($Bluetooth) || empty($WIFI) || empty($UDID)) {
        //         $i++;
        //         continue;
        //     }

        //     // 判断文件中是否有重复的udid
        //     $exist = DB::table('devices')->where(['udid' => $UDID])->first();
        //     if ($exist) {
        //         $r++;
        //         continue;
        //     }

        //     DB::table('devices')->insert([
        //         'imei'          => $IMEI,
        //         'udid'          => $UDID,
        //         'serial_number' => $SerialNumber,
        //         'lanya'         => $Bluetooth,
        //         'mac'           => $WIFI,
        //     ]);
        //     $j++;
        // }
        // echo 'repeat:' . $r . '--bad:' . $i;

        // Redis::set('last_device_id', 9999999999);

        die('1');
    }
}
