<?php

namespace App\Console\Commands\Data;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JiaDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jia:device';

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
        for ($i = 0; $i < 6000; $i++) {
            $time   = microtime(true);
            $ranstr = md5($time);

            $mac = $lanya = [];
            for ($j = 0; $j < 6; $j++) {
                $mac[$j] = $lanya[$j] = substr($ranstr, $j * 2, 2);

                // 最后一位-1
                if ($j == 5) {
                    $intnum  = hexdec(substr($ranstr, $j * 2, 2));
                    $mac[$j] = dechex($intnum - 1);
                }
            }
            $data = [
                'imei'          => '36' . (rand(1000000000000, 9999999999999)),
                'udid'          => substr($ranstr, 0, 8) . md5($time . rand(1, 100)),
                'serial_number' => strtoupper(substr($ranstr, 8, 12)),
                'os'            => '',
                'mac'           => join(':', $mac),
                'lanya'         => join(':', $lanya),
                'is_real'       => 0,
            ];

            $res = DB::table('devices')->insert($data);
            if ($res) {
                echo "g-{$i}\n";
            } else {
                echo "f-{$i}\n";
            }
        }
    }
}
