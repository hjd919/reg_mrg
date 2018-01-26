<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
            '--file' => $email_path,
            '--appid' => $appid,
        ]);
        $content = ob_get_contents();
        ob_end_clean();
        return response()->json(['exitCode' => $exitCode, 'error' => 0, 'content' => $content]);
    }
}
