<?php

namespace App\Console\Commands\Import;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:emails';

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
        $file = './emails_'.date('md').'.csv';
        $fp   = fopen($file, 'r');
        $r    = $i    = $j    = 0;
        while (($data = fgetcsv($fp, 1000, ' ')) !== false) {

            list($email, $appleid_password) = $data;

            // 去除两端空格
            $email            = trim($email);
            $appleid_password = trim($appleid_password);

            if (empty($email) || empty($appleid_password)) {
                $i++;
                continue;
            }

            // 判断文件中是否有重复的udid
            $exist = DB::table('emails')->where([
                'email' => $email,
            ])->first();
            if ($exist) {
                $r++;
                continue;
            }

            DB::table('emails')->insert([
                'email'            => $email,
                'appleid_password' => $appleid_password,
                'password'         => $appleid_password,
                'is_valid'         => 301,
            ]);
            $j++;
        }
        echo 'empty:' . $i . '--good:' . $j . '--repeat:' . $r;
        die("\n ok");
    }
}
