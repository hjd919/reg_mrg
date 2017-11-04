<?php

namespace App\Console\Commands\Import;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ImportDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:devices';

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
        $file = 'devices' . date('md') . '.csv';
        $fp   = fopen($file, 'r');
        $r    = $i    = $j    = 0;
        while (($data = fgetcsv($fp)) !== false) {
            list($SerialNumber, $IMEI, $Bluetooth, $WIFI, $UDID) = $data;
            if (empty($SerialNumber) || empty($IMEI) || empty($Bluetooth) || empty($WIFI) || empty($UDID)) {
                $i++;
                continue;
            }

            // 判断文件中是否有重复的udid
            $exist = DB::table('devices')->where(['udid' => $UDID])->first();
            if ($exist) {
                $r++;
                continue;
            }

            DB::table('devices')->insert([
                'imei'          => $IMEI,
                'udid'          => $UDID,
                'serial_number' => $SerialNumber,
                'lanya'         => $Bluetooth,
                'mac'           => $WIFI,
            ]);
            $j++;
        }
        echo 'repeat:' . $r . '--bad:' . $i;

        Redis::set('last_device_id', 9999999999);

        die('1');
    }
}
