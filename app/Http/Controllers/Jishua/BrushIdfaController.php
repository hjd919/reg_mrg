<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrushIdfaController extends Controller
{

    public function get(
        Request $request
    ) {
        $cb_params   = $request->input('cb_params', '');
        $check_token = '';
        if ($cb_params) {
            $check_token = base64_encode($cb);
        }
        $response      = DB::table('brush_idfas')->find(1);
        $response->ret = 0;

        // 创建任务
        $id = DB::table('brush_idfa_tasks')->insertGetId([
            'brush_idfa_id' => $response->id,
            'idfa'          => $idfa,
            'appid'         => $appid,
            'device_id'     => $device_id,
            'brush_idfa_id' => $brush_idfa_id,
        ]);

        if (!Redis::sIsMember('exist_brush_idfas_stat', $brush_idfa_id)) {
            $id = DB::table('brush_idfas_stat')->insertGetId([
                'brush_idfa_id' => $brush_idfa_id,
                'appid'         => $appid,
            ]);
            Redis::sAdd('exist_brush_idfas_stat', $id);
        }

        if ($response->taskType == 1) {
            $callback_url       = urlencode(url('backend/notify_success?appid=' . $response->appid . '&id=' . $response->id . '&check_token=' . $cb_params));
            $response->callback = str_replace('{callback}', $callback_url, $response->callback);
        }

        return response()->json($response);
    }

    public function notifySuccess(
        Request $request
    ) {
        $check_token   = $request->input('check_token', '');
        $device_id     = $request->input('device_id', '');
        $brush_idfa_id = $request->input('id', '');
        $appid         = $request->input('appid', '');
        $idfa          = $request->input('idfa', '');
        if ($check_token) {
            $check_token = json_decode(base64_decode($check_token));
            if ($check_token) {
                $device_id = $check_token->device_id;
                $idfa      = $check_token->idfa;
            }
        }

        // 累加
        DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_id)->increment('success_idfa_num');

        return $this->success_response(['success' => 1]);
    }

    public function ciliuGet(Request $request)
    {
        // todo 混淆获取id
        $brush_idfa_task = DB::table('brush_idfa_tasks')->where('task_status', 1)->limit(1)->first()->toArray();
        DB::table('brush_idfa_tasks')->where('id', $brush_idfa_task->id)->increment('task_status');

        DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_task->brush_idfa_id)->increment('ciliu_returned');

        $brush_idfa = DB::table('brush_idfas')->select('bundleId,process')->where('id', $brush_idfa_task->brush_idfa_id)->first();

        $brush_idfa_task['bundleId'] = $brush_idfa->bundleId;
        $brush_idfa_task['process']  = $brush_idfa->process;

        return $this->success_response($brush_idfa_task);
    }

    public function ciliuReport(Request $request)
    {
        $id     = $request->input('id', '');
        $status = $request->input('status', '');

        if ($status === '') {
            return response()->json(['ret' => 1]);
        }

        $brush_idfa_task = DB::table('brush_idfa_tasks')->select('brush_idfa_id')->where('id', $id)->first();

        if ($status == 1) {
            DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_task->brush_idfa_id)->increment('ciliu_returned_success');
        }

        $res = DB::table('brush_idfa_tasks')->where('id', $id)->update(['status' => $status]);
        if ($res) {
            return $this->success_response($brush_idfa_task);
        } else {
            return $this->fail_response();
        }
    }
}
