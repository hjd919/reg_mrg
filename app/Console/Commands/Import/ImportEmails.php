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
    protected $signature = 'import:emails {--file=} {--file_type=txt} {--glue=----}';
    protected $import_date;
    protected $i = 0;
    protected $r = 0;
    protected $j = 0;

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
        $this->import_date = date('Y-m-d');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file_type = $this->option('file_type');
        $glue      = $this->option('glue');
        $file      = $this->option('file');

        // $file = './emails_' . date('md') . '.csv';
        // $file = './emails_' . date('md') . '.csv';
        // $file = '/Users/jdhu/Downloads/20171123200040011100650054162040.txt';

        try {
            // 根据file_type去读取文件
            if ($file_type == 'txt') {

                // 1.fopen+feof+fgets
                $file_handle = fopen($file, 'r');
                while (!feof($file_handle)) {
                    $line     = fgets($file_handle);
                    $line_arr = explode($glue, rtrim($line));

                    // * 判重并插入
                    $this->queryAndInsert($line_arr);
                }
                fclose($file_handle);

                // 2.file()
                // $file_arr = file($file);
                // foreach ($file_arr as $line_no => $line) {
                //     $line_arr = explode($glue, rtrim($line));

                //     // * 判重并插入
                //     $this->queryAndInsert($line_arr);
                // }

            } elseif ($file_type == 'csv') {
                $fp = fopen($file, 'r');
                while (($line_arr = fgetcsv($fp, 1000, ';')) !== false) {
                    // * 判重并插入
                    $this->queryAndInsert($line_arr);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        echo json_encode([
            'success' => $this->j,
            'repeat'  => $this->r,
            'empty'   => $this->i,
        ]);

        return true;
    }

    // * 判重并插入
    public function queryAndInsert($line_arr)
    {
        $import_date = $this->import_date;
        // 判断值
        if (!isset($line_arr[1])) {
            return true;
        }
        list($email, $appleid_password) = $line_arr;
        // 去除两端空格
        $email            = trim($email);
        $appleid_password = trim($appleid_password);

        // 判断输入是否为空
        if (empty($email) || empty($appleid_password)) {
            $this->i++;
            return true;
        }

        // 判断文件中是否有重复的udid
        $exist = DB::table('emails')->where([
            'email' => $email,
        ])->first();
        if ($exist) {
            $this->r++;
            return true;
        }

        DB::table('emails')->insert([
            'email'            => $email,
            'appleid_password' => $appleid_password,
            'import_date'      => $import_date,
        ]);
        $this->j++;
        return true;
    }
}
