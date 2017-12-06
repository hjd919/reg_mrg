<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ToMaxMinId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to:max_min_id';

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
        // * to device_id
        $rows = DB::table('apps')->groupBy('appid')->get();
        if ($rows->isEmpty()) {
            return false;
        }

        foreach ($rows as $row) {
            $max_min_account_id = WorkDetail::getMinMaxAccountId($row->appid);
            DB::table('ios_apps')->where('appid', $row->appid)->update($max_min_account_id);
        }
    }
}
