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
    protected $signature = 'export_delete:invalid_email';

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
        // 导出并删除失效账号
        $code = exec("mysqldump -uhjd -p'hjd2015' -h127.0.0.1 -P3306 --default-character-set=utf8 --no-create-db --no-create-info --tables jishua emails --where='valid_status=0' > jishua_emails.table.sql");
    }
}
