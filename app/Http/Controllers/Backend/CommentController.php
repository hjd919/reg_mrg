<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CommentController extends BackendController
{
    public function import(Request $request)
    {
        set_time_limit(0);

        $appid = $request->appid;

        // 文件名
        $extension   = $request->upload_email->extension();
        $upload_name = time() . '.' . $extension;

        // 上传文件
        $path = $request->upload_email->storeAs('import_comments', $upload_name);
        if (!$path) {
            return response()->json(['error' => 1]);
        }
        // 导入
        $email_path = storage_path('app/' . $path);

        ob_start();
        $exitCode = Artisan::call('import:comments', [
            '--file'  => $email_path,
            '--appid' => $appid,
        ]);
        $content = ob_get_contents();
        ob_end_clean();
        return response()->json(['exitCode' => $exitCode, 'error' => 0, 'content' => $content]);
    }

    public function clear(Request $request)
    {
        $appid = $request->input('appid');
        if (!$appid) {
            return response()->json(['error' => '缺少appid']);
        }

        DB::beginTransaction();

        $useful_comment_id_key = "useful_comment_ids:appid_{$appid}";
        $ids                   = Redis::sMembers($useful_comment_id_key);
        // $ids = DB::table('comments')->select('id')->where(compact('appid'))->pluck('id');
        $count_ids             = count($ids);
        $useful_comment_id_key = "useful_comment_ids:appid_{$appid}";
        $s2                    = $s1                    = $f                    = 0;
        foreach ($ids as $comment_id) {
            $s2 = DB::table('comments')->where('id', $comment_id)->delete();
            if (!$s2) {
                $f++;
            } else {
                $s1++;
            }
        }
        if ($count_ids == $s1) {
            $res = Redis::delete($useful_comment_id_key);
            DB::commit();
            return response()->json(['error' => 0, 'content' => "清除了{$s1}条老数据"]);
        } else {
            DB::rollback();
            return response()->json(['error' => 1, 'content' => "清除失败"]);
        }

    }
}
