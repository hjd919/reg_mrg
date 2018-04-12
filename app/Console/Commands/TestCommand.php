<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
    The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'static:ip';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送邮件命令';
    protected $i;
    protected $ports = [14202, 14203, 14204];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function count_proxy()
    {

        $username  = "cn_xs";
        $did       = 'did';
        $uid       = md5(microtime(true) . uniqid() . rand(1, 9999));
        $pid       = -1;
        $cid       = -1;
        $timestamp = time();
        $key       = "Al0MF4fizqjbM9Ql";

        $str1 = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&key={$key}";
        $sign = md5($str1);
        $pwd  = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&sign={$sign}";

        return $pwd;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $i = 1;
        while (1) {

            // 统计一天的ip量和地区
            // usleep(500);
            echo date('Y-m-d H:i:s') . "--{$i}\n";
            $this->i = $i;
            $i++;
            $h = $this->curl(); // 下载文件
            $this->deal($h);
            // $html = $this->curl(); // 下载文件
            // foreach ($html as $h) {
            //     $this->deal($h);
            // }
        }
    }

    public function deal($html)
    {
        $html    = iconv('gb2312', 'utf-8', $html);
        $pattern = "#您的IP是：\[(.*)\] 来自：(.*?)<\/#";
        if (preg_match($pattern, $html, $matches)) {
            $res = DB::table('ips')->insert([
                'ip'   => $matches[1],
                'area' => $matches[2],
            ]);
            if (!$res) {
                echo 'insert error';
            }
        } else {
            $res = DB::table('ip_errors')->insert([
                'html' => $html,
            ]);

        }
        return true;
    }

    public function curl()
    {
        /*    for ($j = 0; $j < 2; $j++) {
        $pwd[$j] = $this->count_proxy();
        }

        // url
        $chArr = [];
        for ($i = 0; $i < 2; $i++) {
        $chArr[$i] = curl_init("http://2017.ip138.com/ic.asp");
        curl_setopt($chArr[$i], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        // curl_setopt($chArr[$i], CURLOPT_PROXY, "47.94.225.221:14202");
        curl_setopt($chArr[$i], CURLOPT_PROXY, "118.31.212.185:14202");
        curl_setopt($chArr[$i], CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd[$i]}");
        curl_setopt($chArr[$i], CURLOPT_RETURNTRANSFER, 1);
        }

        $mh = curl_multi_init(); //1
        foreach ($chArr as $k => $ch) {
        curl_multi_add_handle($mh, $ch); //2
        }
        $running = null;
        do {
        curl_multi_exec($mh, $running); //3
        } while ($running); //4

        foreach ($chArr as $k => $ch) {
        $output[$k] = curl_multi_getcontent($ch); //5
        curl_multi_remove_handle($mh, $ch); //6
        }

        curl_multi_close($mh); //7 */
        $pwd = $this->count_proxy();

        $i     = $this->i;
        $ports = $this->ports;
        $n     = $i % 3;
        // echo "118.31.212.185:{$ports[$n]}\n";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14204");
        // curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:{$ports[$n]}");
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");
        curl_setopt($curl, CURLOPT_URL, 'http://2017.ip138.com/ic.asp');
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }
}
