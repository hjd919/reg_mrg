<?php
namespace App\Http\Controllers\Appleid;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    public function querySuccess(Request $request)
    {
        $success_num = DB::table('appleids')->where('state', 1)->count();
        return response()->json([
            'success_num' => $success_num,
        ]);
    }

    // 获取代理
    public function getproxy()
    {
        $uid1 = Redis::get('proxy_ip_uid');
        Redis::incr('proxy_ip_uid');
        $uid1 = intval($uid1);
        $uid  = md5($uid1);
        // $uid       = 'uid';
        $did       = 'did';
        $pid       = -1;
        $cid       = -1;
        $timestamp = time();
        $key       = "Al0MF4fizqjbM9Ql";

        $str1 = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&key={$key}";
        $sign = md5($str1);
        $pwd  = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&sign={$sign}";
        // $pwd2 = "{$username}:{$pwd}";
        // $auth = base64_encode($pwd2);

        // 新uid 还是用 旧uid
        // log
        $id = DB::table('proxy_uids')->insertGetId([
            'uid' => $uid1,
            'pwd' => $pwd,
        ]);

        $res = [
            "id"       => $id,
            "ip"       => "118.31.212.185",
            "port"     => "14202",
            "user"     => "cn_xs",
            "password" => $pwd,
            "type"     => "sock5",
        ];
        return response()->json($res);
    }

    // * 获取苹果验证吗
    public function getverifycode(
        Request $request
    ) {

        $start_time = microtime(true);
        $email      = $request->email;
        $password   = $request->pas;
        if (!$email || !$password) {
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'pas或者email缺少',
                'code'   => '',
            ]);
        }
        Util::log('--start--', json_encode(compact('email')));
        list($username, $email_host) = explode('@', $email);

        // * 获取请求地址配置信息
        $port = '995';

        // 代理 一分钟才切换ip
        // $pwd = Redis::get('proxy_pwd');
        // if (!$pwd) {
        $username  = "cn_xs";
        $did       = 'did';
        $uid       = md5(time());
        $pid       = 0;
        $cid       = 0;
        $timestamp = time();
        $key       = "Al0MF4fizqjbM9Ql";

        $str1 = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&key={$key}";
        $sign = md5($str1);
        $pwd  = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&sign={$sign}";

        // Redis::set('proxy_pwd', $pwd);
        // Redis::expire('proxy_pwd', 60);
        // }

        // 获取列表
        // $list = Pop3::getAppleEmail($email, $password, $content_id = '');
        exec("php ./pop3_list.php {$email} {$password} pop3s://pop.mail.ru/ {$port} '{$pwd}'", $output);
        if (empty($output[0])) {
            // 标志该邮箱不能用

            DB::table('appleids')->where('strRegName', $email)->update(['state' => 5]);

            return response()->json([
                'errno'  => 2,
                'errmsg' => "php ./pop3_list.php {$email} {$password} pop3s://pop.mail.ru/ {$port} '{$pwd}'",
                'code'   => '',
            ]);
        }
        $content_ids = json_decode($output[0]);

        $get_email_content = function ($email, $password, $content_id) use ($email_host, $port, $pwd) {
            switch ($email_host) {
                case 'qq.com':
                    $comand_url = 'pop3s://pop.qq.com/' . $content_id;
                    break;
                case 'mail.ua':
                case 'mail.ru':
                    $comand_url = 'pop3s://pop.mail.ru/' . $content_id;
                    break;
                default:
                    return false;
                    break;
            }
            exec("php ./pop3_content.php {$email} {$password} {$comand_url} {$port} '{$pwd}'", $output);
            // Util::log('output:' . $content_id, $output);
            return isset($output[0]) ? $output[0] : $output;
        };

        // 循环获取邮件内容
        $verify_code = '';
        $content_ids = array_reverse($content_ids); //取最新的邮箱
        foreach ($content_ids as $content_id) {
            $verify_code = $get_email_content($email, $password, $content_id);
            if ($verify_code) {
                break;
            }
            // $content = POP3::getAppleEmail($email, $password, $content_id);
        }
        if (!$verify_code) {
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'not find code' . json_encode(['email_pwd' => $password]),
                'code'   => '',
            ]);
        }
        $end_time = microtime(true);
        Util::log('--end--', json_encode([
            'email'      => $email,
            'spend_time' => $end_time - $start_time,
        ]));

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
        // * 查询未获取的任务
        $row = DB::table('appleids')->where('state', 0)
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

        // * 更新状态
        DB::table('appleids')->where('id', $row->id)->update(['state' => 3]);

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

        // * 根据任务id和账号id更新刷任务记录状态
        DB::table('appleids')->where('strRegName', $email)->update([
            'state' => $state,
        ]);

        return response()->json([
            "errno"  => 0,
            "errmsg" => "success",
            "code"   => "",
            "data"   => (object) [],
        ]);
    }
}
