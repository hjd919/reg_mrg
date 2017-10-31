<?php

namespace App\Console\Commands\Import;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $file = 'devices.csv';
        $fp   = fopen($file, 'r');
        $i    = $j    = 0;
        while (($data = fgetcsv($fp)) !== false) {
            list($SerialNumber, $IMEI, $Bluetooth, $WIFI, $UDID) = $data;
            if (empty($SerialNumber) || empty($IMEI) || empty($Bluetooth) || empty($WIFI) || empty($UDID)) {
                $i++;
                continue;
            }

            // 判断文件中是否有重复的udid
            $exist = DB::table('mobile_info')->where(['udid' => $UDID])->first();
            if ($exist) {
                continue;
            }

            DB::table('mobile_info')->insert([
                'imei'          => $IMEI,
                'udid'          => $UDID,
                'serial_number' => $SerialNumber,
                'lanya'         => $Bluetooth,
                'mac'           => $WIFI,
            ]);
            $j++;
        }
        echo 'good:' . $i . '--bad:' . $j;
        die('1');
    }
}
