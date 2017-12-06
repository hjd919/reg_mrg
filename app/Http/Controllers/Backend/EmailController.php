<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EmailController extends BackendController
{
    public function import(Request $request)
    {
        // 文件名
        $extension   = $request->upload_email->extension();
        $upload_name = time() . '.' . $extension;

        // 上传文件
        $path = $request->upload_email->storeAs('import_emails', $upload_name);
        if (!$path) {
            return response()->json(['error' => 1]);
        }

        // 导入email
        $email_path = storage_path('app/' . $path);
        ob_start();
        $exitCode = Artisan::call('import:emails', [
            '--file' => $email_path,
        ]);
        $content = ob_get_contents();
        ob_end_clean();
        return response()->json(['exitCode' => $exitCode, 'error' => 0, 'content' => $content]);
    }
}
