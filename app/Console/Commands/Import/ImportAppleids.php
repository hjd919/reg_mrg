<?php

namespace App\Console\Commands\Import;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAppleids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:appleids {--file=} {--file_type=txt}  {--glue=----}';
    protected $i         = 0;
    protected $k         = 0;
    protected $r         = 0;
    protected $j         = 0;

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
        $file      = $this->option('file');
        $glue      = $this->option('glue');
        $file_type = $this->option('file_type');

        if ($file_type == 'txt') {
            // 顺序读取
            // 1.file()
            // $file = file($file);

            // 2.fopen() ->fgetcsv|feof()+fgets() -> fclose
            $file_handle = fopen($file, 'r');
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $data = explode($glue, rtrim($line));
                $res  = $this->handleLineArr($data);
                if (!$res) {
                    echo '该文件内容格式不对';
                    break;
                }
            }
            fclose($file_handle);
            // 跳跃读取
        } elseif ($file_type == 'csv') {
            $file_handle = fopen($file, 'r');

            $fp = fopen($file, 'r');
            $r  = $i  = $j  = 0;
            while (($data = fgetcsv($fp, 1000, ';')) !== false) {
                $res = $this->handleLineArr($data);
                if (!$res) {
                    echo '该文件内容格式不对';
                    break;
                }
            }
        }
        echo 'empty:' . $this->i . '--good:' . $this->j . '--repeat:' . $this->r . '--geshi:' . $this->k;
        die("\n ok");
    }

    public function handleLineArr($line_arr)
    {
        if (!isset($line_arr[1])) {
            $this->k++;
            return true;
        }

        list($email, $appleid_password) = $line_arr;

        // 去除两端空格
        $email            = trim($email);
        $appleid_password = trim($appleid_password);

        if (empty($email) || empty($appleid_password)) {
            $this->i++;
            return true;
        }

        // 判断文件中是否有重复的udid
        $exist = DB::table('appleids')->where([
            'strRegName' => $email,
        ])->first();
        if ($exist) {
            $this->r++;
            return true;
        }

        // 随机
        $nRandomYear  = rand(1970, 2000);
        $nRandomMonth = rand(1, 12);
        $nRandomDay   = rand(1, 29);

        $nRandomYear = rand(1970, 2000);
        $nRandomYear = rand(1970, 2000);

        DB::table('appleids')->insert(array(
            'pwd'           => $appleid_password,
            'country'       => '0',
            'strQ1'         => 0,
            'strQ2'         => 0,
            'strQ3'         => 0,
            'strAn1'        => '天气',
            'strAn2'        => '真的',
            'strAn3'        => '好啊',
            'strRegPwd'     => 'Td445544',
            'nRandomYear'   => $nRandomYear,
            'nRandomMonth'  => $nRandomMonth,
            'nRandomDay'    => $nRandomDay,
            'strA1'         => '广东',
            'strA2'         => '韶关市',
            'strA3'         => '广东省韶关市连山壮族瑶族自治县江区东堤北路' . rand(1, 1000) . '号',
            'strDeviceGUID' => rand(10, 99) . 'dbcaf8' . md5(microtime(true)),
            'strFirstName'  => '亚环' . rand(1, 99),
            'strLastName'   => '喻' . rand(1, 99),
            'strRegName'    => $email,
            'strPhone'      => '1507079' . rand(1000, 9999),
        ));
        $this->j++;
        return true;
    }
}
