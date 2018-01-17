<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BackupInvalidEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:invalid_emails';

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

        // 导出
        $date = date('ymd');
        $code = exec("mysqldump -u'super_hjd' -p'Dev~!@#Hjd919' -P3306 --default-character-set=utf8 --no-create-db --no-create-info --tables jishua emails --where='valid_status=0' > storage/app/backup/jishua_emails_{$date}.table.sql");

        // 删除
        $res = DB::table('emails')->where('valid_status', 0)->delete();
        echo ('$res---');
        var_dump($res);

        // 导入无效账号

    }
}
