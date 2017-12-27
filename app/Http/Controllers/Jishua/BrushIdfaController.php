<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BrushIdfaController extends Controller
{

    public function get(
        Request $request
    ) {
        $idfa      = $request->input('idfa');
        $device_id = $request->input('device_id');
        if (!$idfa || !$device_id) {
            return $this->fail_response(['message' => '缺少参数cb_params']);
        }

        $response      = DB::table('brush_idfas')->find(1);
        $response->ret = 0;

        // 创建任务 brush_idfa_tasks
        $brush_idfa_id = $response->id;
        $appid         = $response->appid;
        $tid           = DB::table('brush_idfa_tasks')->insertGetId([
            'idfa'          => $idfa,
            'appid'         => $appid,
            'device_id'     => $device_id,
            'brush_idfa_id' => $brush_idfa_id,
        ]);

        // 创建统计表
        if (!Redis::sIsMember('exist_brush_idfas_stat', $brush_idfa_id)) {
            $brush_idfas_stat_id = DB::table('brush_idfas_stat')->insertGetId([
                'brush_idfa_id' => $brush_idfa_id,
                'appid'         => $appid,
            ]);
            Redis::sAdd('exist_brush_idfas_stat', $brush_idfas_stat_id);
        }
        DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_id)->increment('returned');

        $cb_params = json_encode(compact('idfa', 'device_id'));

        // 回调任务，拼接回调地址
        if ($response->taskType == 1) {
            $callback_url       = urlencode(url('backend/notify_success?tid=' . $tid . '&appid=' . $appid . '&bid=' . $brush_idfa_id . '&check_token=' . base64_encode($cb_params)));
            $response->callback = str_replace('{callback}', $callback_url, $response->callback);
        }

        return response()->json($response);
    }

    public function notifySuccess(
        Request $request
    ) {
        $check_token   = $request->input('check_token', '');
        $device_id     = $request->input('device_id', '');
        $brush_idfa_id = $request->input('bid', '');
        $task_id       = $request->input('tid', '');
        $appid         = $request->input('appid', '');
        $idfa          = $request->input('idfa', '');
        if ($check_token) {
            $check_token = json_decode(base64_decode($check_token));
            if ($check_token) {
                $device_id = $check_token->device_id;
                $idfa      = $check_token->idfa;
            }
        }

        DB::table('brush_idfa_tasks')->where('id', $task_id)->update(['task_status' => 1]);

        // 累加
        DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_id)->increment('success_idfa_num');

        return $this->success_response(['success' => 1]);
    }

    public function ciliuGet(Request $request)
    {
        // todo 混淆获取id
        $brush_idfa_task = DB::table('brush_idfa_tasks')->where('task_status', 1)->limit(1)->first();
        if (!$brush_idfa_task) {
            return $this->fail_response(['message' => 'no more brush_idfa_task']);
        }

        DB::table('brush_idfa_tasks')->where('id', $brush_idfa_task->id)->increment('task_status');

        DB::table('brush_idfas_stat')->where('brush_idfa_id', $brush_idfa_task->brush_idfa_id)->increment('ciliu_returned');

        $brush_idfa = DB::table('brush_idfas')->select('bundleId,process')->where('id', $brush_idfa_task->brush_idfa_id)->first();

        $brush_idfa_task->bundleId = $brush_idfa->bundleId;
        $brush_idfa_task->process  = $brush_idfa->process;

        return $this->success_response((array) $brush_idfa_task);
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
