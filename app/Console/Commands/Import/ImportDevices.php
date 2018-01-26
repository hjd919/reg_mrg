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
        $file      = './comments.txt';
        $db        = DB::connection('mysql4');
        $usernames = $db->table('users')->select('user_name')->where('id', '>', 10000)->limit(300)->pluck('user_name')->toArray();
        $fp        = fopen($file, 'r');
        $r         = $i         = $j         = 0;
        while (($data = fgetcsv($fp)) !== false) {

            list($title, $content) = $data;
            /*if (empty($SerialNumber) || empty($IMEI) || empty($Bluetooth) || empty($WIFI) || empty($UDID)) {
            $i++;
            continue;
            }*/

            // 判断文件中是否有重复的udid
            // $exist = DB::table('comments')->where(['udid' => $UDID])->first();
            // if ($exist) {
            //     $r++;
            //     continue;
            // }

            DB::table('comments')->insert([
                'title'    => $title,
                'content'  => $content,
                'nickname' => $usernames[$j],
                'app_id'   => 9765,
            ]);
            $j++;
        }
        echo 'good:' . $j;
        die('1');
    }
}
