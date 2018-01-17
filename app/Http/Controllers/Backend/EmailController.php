<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class EmailController extends BackendController
{
    public function stateImport(
        Request $request
    ) {
        // 统计导入列表
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);

        // 统计导入
        $where = [
            ['import_date', '>=', date('Y-m-d', strtotime('-2 weeks'))],
            ['source', '=', 1],
        ];
        // $total      = Email::where($where)->get(); //total
        $total      = 10; //total
        $pagination = [
            'current'  => (int) $current_page,
            'pageSize' => (int) $page_size,
            'total'    => (int) $total,
        ];

        $offset = ($current_page - 1) * $page_size;
        $list   = DB::connection('stat')->table('emails')->where($where)
            ->selectRaw("count(*) total,import_date")
            ->groupBy('import_date')
            ->orderBy('import_date', 'desc')
            ->limit($page_size)
            ->offset($offset)
            ->get(); // list

        return response()->json(compact('pagination', 'list'));
    }

    public function getTodayNum()
    {
        $today_num = DB::table('emails')->where('create_time', '>=', date('Y-m-d'))->where('source', 1)->count();
        return response()->json(['today_email_num' => $today_num]);
    }

    public function import(Request $request)
    {
        set_time_limit(0);

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
