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

    protected $areas        = ['北京', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海'];
    protected $city         = ['北京市', '上海市', '广州市', '深圳市', '成都市', '杭州市', '武汉市', '重庆市', '南京市', '天津市', '苏州市', '西安市', '长沙市', '沈阳市', '青岛市', '郑州市', '大连市', '东莞市', '宁波市', '厦门市', '福州市', '无锡市', '合肥市', '昆明市', '哈尔滨市', '济南市', '佛山市', '长春市', '温州市', '石家庄市', '常州市', '泉州市', '南昌市', '贵阳市', '太原市', '烟台市', '嘉兴市', '南通市', '金华市', '珠海市', '惠州市', '徐州市', '海口市', '绍兴市', '中山市',  '兰州市'];
    protected $road         = ['中山', '胜利', '解放', '斯大林', '列宁', '人民', '振兴', '团结', '胜利', '建设', '和平', '幸福', '光明', '平安'];
    protected $phone_prefix = ['135', '150', '185', '187'];
    protected $string       = "搞几个汉字数组里面随机取几个在加几个字符理论上是不重复的";

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
        echo json_encode([
            'empty'      => $this->i,
            'good'       => $this->j,
            'repeat'     => $this->r,
            'line_error' => $this->k,
        ]);
        return true;
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
        $nRandomYear  = rand(1970, 2010);
        $nRandomMonth = rand(1, 12);
        $nRandomDay   = rand(1, 29);

        $nRandomYear = rand(1970, 2000);
        $nRandomYear = rand(1970, 2000);

        $areas = $this->areas[rand(0, 22)];
        $city  = $this->city[rand(0, 45)];
        $road  = $this->road[rand(0, 13)];

        DB::table('appleids')->insert(array(
            'pwd'           => $appleid_password,
            'country'       => '0',
            'strQ1'         => 0,
            'strQ2'         => 0,
            'strQ3'         => 0,
            'strAn1'        => $areas,
            'strAn2'        => $city,
            'strAn3'        => $road,
            'strRegPwd'     => 'Td' . rand(100000, 999999),
            'nRandomYear'   => $nRandomYear,
            'nRandomMonth'  => $nRandomMonth,
            'nRandomDay'    => $nRandomDay,
            'strA1'         => $areas,
            'strA2'         => $city,
            'strA3'         => $areas . $city . $road . '路' . rand(1, 1000) . '号',
            'strDeviceGUID' => substr(md5($nRandomYear), 3, 8) . md5(microtime(true)),
            'strFirstName'  => mb_substr($this->string, rand(0, 15), rand(1, 2)),
            'strLastName'   => mb_substr($this->string, rand(0, 15), rand(2, 4)),
            'strRegName'    => $email,
            'strPhone'      => $this->phone_prefix[rand(0, 3)] . rand(10000000, 99999999),
        ));
        $this->j++;
        return true;
    }
}
