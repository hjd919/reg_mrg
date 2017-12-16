<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class sAddAppleids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sAdd:appleids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时添加新账号到缓存中';

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
        $email_max_id = Redis::get('email_max_id');
        $total_key = 'valid_account_ids';
        
        die((string)Redis::sCard($total_key));

        $emails = DB::table('emails')
            ->select('id')
            ->where('id', '>', $email_max_id)
            ->where('valid_status', 1)->get();
        // $i = 0;
        if($emails->isEmpty()){
            die;
        }
        foreach ($emails as $r) {
            $res = Redis::sAdd($total_key, $r->id);
            // print_r($res) . "\n";
            // if ($res) {
            //     $i++;
            // }
        }

        Redis::set('email_max_id', $r->id);
    }
}
