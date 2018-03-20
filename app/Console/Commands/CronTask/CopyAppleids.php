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
        $date           = date('Y-m-d H');
        while (1) {
            $rows = DB::table('appleids')->where([
                ['updated_at', '>=', $date],
                ['state', '=', 1],
            ])
                ->offset($offset)
                ->limit($len)
                ->select('strRegName', 'strRegPwd', 'id')
                ->get();
            if ($rows->isEmpty()) {
                break;
            }

            $emails = [];
            foreach ($rows as $row) {
                $emails = [
                    'email'            => $row->strRegName,
                    'appleid_password' => $row->strRegPwd,
                    'import_date'      => $import_date,
                    'source'           => 2,
                ];
                $res = $prod_jishua_db->table('emails')->insert($email);
                if (!$res) {
                    echo "error\n";
                } else {
                    DB::table('appleids')->where('id', $row->id)->update(['state' => 200]);
                }
            }

            $offset += $len;
            echo "offset:$offset\n";
        }
    }
}
