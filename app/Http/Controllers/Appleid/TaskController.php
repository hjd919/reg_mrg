<?php
namespace App\Http\Controllers\Appleid;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected $ports = [14202, 14203, 14204];

    public function getCommandUrl($email_host, $content_id = '')
    {
        switch ($email_host) {
            case 'qq.com':
                $comand_url = 'pop3s://pop.qq.com/' . $content_id;
                break;
            case 'mail.ua':
            case 'list.ru':
            case 'bk.ru':
            case 'inbox.ru':
            case 'mail.ru':
                $comand_url = 'pop3s://pop.mail.ru/' . $content_id;
                break;
            case 'hotmail.com':
                $comand_url = 'pop3s://pop-mail.outlook.com/' . $content_id;
                break;
            case 'tom.com':
                $comand_url = 'pop3s://pop.tom.com/' . $content_id;
                break;
            default:
                return false;
                break;
        }
        return $comand_url;
    }

    public function querySuccess(Request $request)
    {
        $success_num = DB::table('appleids')->where('state', 1)->count();
        return response()->json([
            'success_num' => $success_num,
        ]);
    }

    // 获取代理
    public function getproxy2()
    {
        $uid1 = uniqid();

        $uid       = md5($uid1 . microtime(true) . rand(1, 1000));
        $did       = 'did';
        $pid       = -1;
        $cid       = -1;
        $uuid      = $uid . rand(1000, 9999);
        $timestamp = time();
        $key       = "Al0MF4fizqjbM9Ql";

        $sign = md5("did={$did}&uid={$uid}&sid=-1&pid={$pid}&cid={$cid}&uuid={$uuid}&&t={$timestamp}&key={$key}");
        $pwd  = "did={$did}&uid={$uid}&sid=-1&pid={$pid}&cid={$cid}&uuid={$uuid}&t={$timestamp}&sign={$sign}";
        // $pwd2 = "{$username}:{$pwd}";
        // $auth = base64_encode($pwd2);

        // 新uid 还是用 旧uid
        // log
        /*$id = DB::table('proxy_uids')->insertGetId([
        'created_at' => date('Y-m-d H:i:s'),
        // 'pwd' => $pwd,
        ]);*/

        $res = [
            "id"       => 1,
            "ip"       => "47.74.174.69",
            "port"     => "14202",
            "user"     => "cn_xs",
            "password" => $pwd,
            "type"     => "sock5",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_PROXY, "47.74.174.69:14202");
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");
        curl_setopt($curl, CURLOPT_URL, 'http://2017.ip138.com/ic.asp');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        dd($output);
        return response()->json($res);
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

    // 获取代理
    public function getproxy()
    {
        $pwd = $this->count_proxy();

        $n     = time() % 3;
        $ports = $this->ports;

        $res = [
            "id"       => 1,
            "ip"       => "118.31.212.185",
            // "port"     => "14204",
            "port"     => (string) $ports[$n],
            "user"     => "cn_xs",
            "password" => $pwd,
            "type"     => "sock5",
        ];
        /*
        $port = rand(10000,20000);
        $res = [
        "id"       => 1,
        "ip"       => "61.160.234.16",
        "port"     => $port,
        "user"     => "",
        "password" => "",
        "type"     => "sock5",
        ];
         */
        // $pwd = 'did=did&uid=a2b076142b75f62d274eebc71e98e5aa&pid=-1&cid=-1&t=1521452013&sign=aa77c1741a3da08494805da881fc6f6a';
        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        // curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14202");
        // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");
        // curl_setopt($curl, CURLOPT_URL, 'http://2017.ip138.com/ic.asp');
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // $output = curl_exec($curl);
        // curl_close($curl);
        // // print_r($res);
        // dd($output);

        //Util::log('proxy',$res);

        return response()->json($res);
    }

    private function getHainanCode($username, $proxy_auth = '')
    {
        exec("casperjs --web-security=no ../casperjs/login_ty.js --email_name='{$username}'", $output);
        //system("casperjs --web-security=no ../casperjs/login_ty.js --email_name='f0f308'", $output);
        if (empty($output)) {
            return response()->json([
                'errno'  => 3,
                'errmsg' => 'nofind',
                'code'   => $output,
            ]);
        }
        return response()->json([
            'errno'  => 0,
            'errmsg' => 'success',
            'code'   => $output[0],
        ]);
    }

    private function mailList($email, $password, $pwd, $comand_url)
    {
        exec("php ./pop3_list.php {$email} {$password} {$comand_url} 995 '{$pwd}'", $output);
        if (empty($output[0])) {
            return false;
        }
        return $output;
    }

    private function mailContent($email, $password, $pwd, $comand_url)
    {
        exec("php ./pop3_content.php {$email} {$password} {$comand_url} 995 '{$pwd}'", $output);

        if (empty($output[0])) {
            return false;
        }
        return $output[0];
    }

    // ru列表内容
    private function mailru()
    {
        return [20000, 23000];
    }
    private function inboxru()
    {
        return [20000, 23000];
    }
    private function bkru()
    {
        return [20000, 23000];
    }
    private function listru()
    {
        return [20000, 23000];
    }
    private function mailua()
    {
        return [20000, 23000];
    }

    private function tomcom()
    {
        return [20000, 30000];
    }

    private function hotmailcom()
    {
        return [50000, 63000];
    }

    // * 获取苹果验证吗
    public function getverifycode(
        Request $request
    ) {
        $email    = $request->email;
        $password = $request->pas;
        if (!$email || !$password) {
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'pas或者email缺少',
                'code'   => '',
            ]);
        }
        //file_get_contents('aaa',json_encode(compact('email','password')),FILE_APPEND);
        list($username, $email_host) = explode('@', $email);

        // 获取代理pwd
        //$pwd = $this->count_proxy();
        $pwd = '';

        // 获取yanbox的imap验证码
        if ($email_host == 'yandex.ru') {
            exec("php ./imap.php {$email} {$password}", $output);

            if (empty($output)) {
                // 没找到邮件列表
                return response()->json([
                    'errno'  => 1,
                    'errmsg' => 'no',
                    'code'   => '',
                ], 555);
            }
            $res = $output[0];
            if ($res === 'is_feng') {
                DB::table('appleids')->where('strRegName', $email)->update(['state' => 404]);
                return response()->json([
                    'errno'  => 1,
                    'errmsg' => 'no',
                    'code'   => '',
                ], 558);
            }

            return response()->json([
                'errno'  => 0,
                'errmsg' => 'success',
                'code'   => $res,
            ]);
        }

        // 获取邮箱的pop地址
        $comand_url = $this->getCommandUrl($email_host);

        // 获取邮箱列表内容
        $mailList = $this->mailList($email, $password, $pwd, $comand_url);
        if (!$mailList) {
            $email_row = DB::table('appleids')->select('id', 'get_num')->where('strRegName', $email)->first();
            if ($email_row->get_num >= 2) {
                DB::table('appleids')->where('id', $email_row->id)->update(['state' => 401]);
            }
            // 没找到邮件列表
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'not find email list:' . json_encode(compact('email', 'password', 'comand_url')),
                'code'   => '',
            ], 555);
        }

        // 处理邮箱列表内容
        // $line        = explode("\r\n", $mailList);
        $filter = str_replace('.', '', $email_host);
        if (!method_exists($this, $filter)) {
            dd('没有这个邮箱列表获取规则');
        }
        list($min_len, $max_len) = call_user_func([$this, $filter]);
        $content_ids             = [];
        foreach ($mailList as $l) {
            if (!trim($l)) {
                continue;
            }
            list($content_id, $content_length) = explode(" ", $l);

            if ($content_length >= $min_len && $content_length <= $max_len) {
                $content_ids[] = $content_id;
            }
        }
        if (!$content_ids) {
            // 没找到苹果邮件
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'not find apple email:' . json_encode(compact('email', 'password', 'comand_url')),
                'code'   => '',
            ], 556);
        }

        //取最新的邮件id
        $content_ids = array_reverse($content_ids);

        // 根据content_id获取邮箱内容
        foreach ($content_ids as $content_id) {

            $comand_url = $this->getCommandUrl($email_host, $content_id);

            $verify_code = $this->mailContent($email, $password, $pwd, $comand_url);
            if ($verify_code) {
                break;
            }
        }

        if (!$verify_code) {
            // 没找到苹果邮件中的code
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'not find apple code' . json_encode(compact('email', 'password', 'comand_url', 'content_ids')),
                'code'   => '',
            ], 557);
        }

        return response()->json([
            'errno'  => 0,
            'errmsg' => 'success',
            'code'   => $verify_code,
        ]);
    }

    // 获取任务
    public function get(
        Request $request
    ) {
        if (env('IS_STOP', 0)) {
            return response()->json([
                'regist' => [
                    'errno'  => 1,
                    'errmsg' => 'no email',
                    'data'   => (object) [],
                ],
            ]);
        }

        // * 查询未获取的任务
        $row = DB::table('appleids')->where('state', 0)
            ->orderBy('get_num', 'asc')
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();
        // file_put_contents('aaa', var_export($row, true), FILE_APPEND);
        if (!$row) {
            DB::table('appleids')->where('state', 3)->update(['state' => 0]); //重置为0
            $row = DB::table('appleids')->where('state', 0)
                ->orderBy('get_num', 'asc')
                ->orderBy('id', 'asc')
                ->limit(1)
                ->first();
            if (!$row) {
                // 没有
                return response()->json([
                    'regist' => [
                        'errno'  => 1,
                        'errmsg' => 'no email',
                        'data'   => (object) [],
                    ],
                ]);
            }
        }

        // * 更新状态
        DB::table('appleids')->where('id', $row->id)->increment('get_num', 1, ['state' => 3]);
        // * 返回所需格式的结果
        return response()->json([
            'regist' => [
                [
                    'errno'  => 0,
                    'errmsg' => 'success',
                    'data'   => $row,
                ],
            ],
        ]);
    }

    // 上报任务
    public function report(
        Request $request
    ) {
        // 输入
        $state = $request->state;
        $email = $request->email;

        if (null === $state || null === $email) {
            Util::die_jishua('缺少参数' . $email . $state);
        }

        $update_data = [
            'state' => $state,
        ];
        if ($state == 1) {
            $update_data['successed_at'] = date('Y-m-d H:i:s');
        }

        // * 根据任务id和账号id更新刷任务记录状态
        DB::table('appleids')->where('strRegName', $email)->update($update_data);

        return response()->json([
            "errno"  => 0,
            "errmsg" => "success",
            "code"   => "",
            "data"   => (object) [],
        ]);
    }
}
