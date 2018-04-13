<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AppleidController extends BackendController
{
    public function getTodayNum()
    {
        $today_num = DB::table('appleids')->where('created_at', '>=', date('Y-m-d'))->count();
        return response()->json(['today_appleid_num' => $today_num]);
    }

    public function import(Request $request)
    {
        set_time_limit(0);

        if ($data = $request->input('data')) {
            // ok
            $path = 'import_appleids/' . uniqid() . '.txt';
            Storage::put($path, $data);
        } else {
            // 文件名
            $extension   = $request->upload_email->extension();
            $upload_name = time() . '.' . $extension;

            // 上传文件
            $path = $request->upload_email->storeAs('import_appleids', $upload_name);
            if (!$path) {
                return response()->json(['error' => 1]);
            }
        }

        // 导入email
        $email_path = storage_path('app/' . $path);
        ob_start();
        $exitCode = Artisan::call('import:appleids', [
            '--file' => $email_path,
        ]);
        $content = ob_get_contents();
        ob_end_clean();
        return response()->json(['exitCode' => $exitCode, 'error' => 0, 'content' => $content]);
    }
}
