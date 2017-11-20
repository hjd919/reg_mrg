<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function queryOne(Request $request)
    {
        $appid = $request->input('appid', 0);
        if (!$appid) {
            return response()->json(['error_code' => 1]);
        }

        $ios_app = DB::table('ios_apps')->where('appid', $appid)->first();

        // 统计该app可刷的量
        $usable_brush_num = WorkDetail::getUsableBrushNum($appid);

        return response()->json(compact('ios_app', 'usable_brush_num'));
    }

    public function saveApp(Request $request)
    {
        $appid     = $request->input('appid');
        $app_name  = $request->input('app_name');
        $appuri    = $request->input('appuri');
        $bundle_id = $request->input('bundle_id');

        // 判断app是否存在,不存在则添加
        $app = DB::table('ios_apps')->where('appid', $appid)->first();
        if ($app) {
            return response()->json(['ios_app_id' => $app->id]);
        }

        $res = DB::table('ios_apps')->insertGetId(compact('appid', 'app_name', 'appuri', 'bundle_id'));
        if (!$res) {
            return response()->json(['error_code' => 1]);
        }

        return response()->json(['ios_app_id' => $res]);
    }
}
