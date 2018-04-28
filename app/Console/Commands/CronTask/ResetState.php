<?php

namespace App\Console\Commands\CronTask;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:state';

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
        DB::table('appleids')->where('created_at', '>', date('Y-m-d H', strtotime('-5 hours')))->where('state', 404)->update(['state' => 3]);

        $count  = DB::table('appleids')->where('state', 3)->count();
        $count2 = DB::table('appleids')->where('state', 0)->count();
        if ($count && !$count2) {
            $res = DB::table('appleids')->where('state', 3)->where('get_num', '<=', 3)->update(['state' => 0]);
            echo "结果是:" . $res;
        }
    }
}
