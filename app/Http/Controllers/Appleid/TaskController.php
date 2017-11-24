<?php
namespace App\Http\Controllers\Appleid;

use App\Http\Controllers\Controller;
use App\Support\Pop3;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    const MAX_KEY      = 9999999999;
    const STOP_GET_APP = 'stop_get_app';

    // * 获取苹果验证吗
    public function getverifycode(
        Request $request
    ) {
        $email    = $request->email;
        $password = DB::table('appleids')->select('pwd')->where('strRegName', $email)->value('pwd');
        $list     = Pop3::getAppleEmail($email, $password, $content_id = '');
        if (!$list) {
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'no email list content' . json_encode(['email_pwd' => $password]),
                'code'   => '',
            ]);
        }

        // 查找出在区间(21164-24000)内的邮件id
        $line = explode("\r\n", $list);
        Util::log('列表切割后内容', $line);
        $content_ids = [];
        foreach ($line as $l) {
            if (!trim($l)) {
                continue;
            }
            // id => contentlength
            list($content_id, $content_length) = explode(" ", $l);

            if ($content_length >= 20000 && $content_length <= 24000) {
                $content_ids[] = $content_id;
            }
        }
        if (!$content_ids) {
            $content_ids = range(1, 10);
        }
        Util::log('列表切割后找到苹果邮件content_id', $content_ids);

        // 循环获取邮件内容
        $verify_code = '';
        foreach ($content_ids as $content_id) {
            $content = POP3::getAppleEmail($email, $password, $content_id);

            // 判断是否是苹果邮件
            if (strpos($content, 'x-ds-vetting-token:') === false) {
                continue;
            }

            // 从苹果邮件匹配获取code
            if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
                $verify_code = $match[1];
                break;
            }
        }

        // 如果找不到，就从头来找一遍
        if (!$verify_code) {
            $content_ids = range(1, 10);
            foreach ($content_ids as $content_id) {
                $content = POP3::getAppleEmail($email, $password, $content_id);

                // 判断是否是苹果邮件
                if (strpos($content, 'x-ds-vetting-token:') === false) {
                    continue;
                }

                if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
                    $verify_code = $match[1];
                    break;
                }
            }
            if (!$verify_code) {
                return response()->json([
                    'errno'  => 1,
                    'errmsg' => 'not find code' . json_encode(['email_pwd' => $password]),
                    'code'   => '',
                ]);
            }
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
        // * 查询未获取的任务
        $row = DB::table('appleids')->where('state', 0)
            ->limit(1)
            ->first();
        if (!$row) {
            // 没有
            return response()->json([
                'errno'  => 1,
                'errmsg' => 'no email',
                'data'   => (object) [],
            ]);
        }

        // * 更新状态
        DB::table('appleids')->where('id', $row->id)->update(['state' => 3]);

        // * 返回所需格式的结果
        return response()->json([
            'errno'  => 0,
            'errmsg' => 'success',
            'data'   => $row,
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
