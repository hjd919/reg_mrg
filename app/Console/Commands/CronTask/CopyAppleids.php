<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopyAppleids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:appleids';

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
        $rows = DB::table('appleids')->where('state', 333)->update(['state' => 1]);

        $prod_jishua_db = DB::connection('prod_jishua');
        $import_date    = date('Y-m-d');
        $len            = 100;
        $offset         = 0;
        // $date           = date('Y-m-d');
        $date = '2018-03-18';
        while (1) {
            $rows = DB::table('appleids')->where([
                ['updated_at', '>=', $date],
                ['state', '=', 1],
            ])->offset($offset)->limit($len)->get();
            if ($rows->isEmpty()) {
                break;
            }

            $emails = [];
            foreach ($rows as $row) {
                $emails[] = [
                    'email'            => $row->strRegName,
                    'appleid_password' => $row->strRegPwd,
                    'import_date'      => $import_date,
                    'source'           => 2,
                ];
            }
            $res = $prod_jishua_db->table('emails')->insert($emails);
            if (!$res) {
                echo "error\n";
            }

            $offset += $len;
            echo "offset:$offset\n";
        }
        die;

        $last_success_time = DB::table('config')->where('keyword', 'last_success_time')->value('value');

        // 获取超时未任务
        $appleids = DB::table('appleids')->where([
            ['state', '=', 1],
            ['updated_at', '>=', $last_success_time],
        ])->get();
        if ($appleids->isEmpty()) {
            // 获取不到，退出
            return true;
        }
        $now_date = date('Y-m-d');
        // DB::statement("
        //     insert into emails (email,appleid_password,import_date,source)
        //     select strRegName,strRegPwd,'{$now_date}',2 from appleids where state=1 and updated_at >= '{$last_success_time}'
        // ");
        foreach ($appleids as $appleid_row) {
            try {
                DB::table('emails')->insert([
                    'email'            => $appleid_row->strRegName,
                    'appleid_password' => $appleid_row->strRegPwd,
                    'import_date'      => $now_date,
                    'source'           => 2,
                ]);
            } catch (\Exception $e) {

            }
        }

        $now = date('Y-m-d H:i:s');
        DB::table('config')->where('keyword', 'last_success_time')->update(['value' => $now]);
    }
}
