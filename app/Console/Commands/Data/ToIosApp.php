<?php

namespace App\Console\Commands\Data;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ToIosApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to:ios_app';

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
        $i    = 0;
        $rows = DB::table('apps')->groupBy('appid')->get();
        if ($rows->isEmpty()) {
            return false;
        }

        foreach ($rows as $row) {
            DB::table('ios_apps')->insert([
                'appid'     => $row->appid,
                'app_name'  => $row->app_name,
                'bundle_id' => $row->bundle_id,
            ]);
            $i++;
            echo '执行' . $i . '次' . "\n";
        }
    }
}
