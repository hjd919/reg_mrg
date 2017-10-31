<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Controller;
use App\Models\TaskApp;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    // 试玩app列表
    public function getTaskApps(
        Request $request
    ) {
        // $doing_apps = Redis::set("doing:app_id:u_11", 1);
        // $doing_apps = Redis::expire("doing:app_id:u_11", 10);
        // dd($doing_apps);
        // die;
        $user = Auth::user();
        if (!$user) {
            Util::die_json('请先登录', 1000);
        }
        $user_id = $user->id;

        // 获取所有上架的app 开始 未开始
        $now_apps = TaskApp::where('is_show', 1)->get();

        // 获取用户正在做的app_id
        $doing_app_key = Util::cacheKey('doing_app_id', ['user_id' => $user_id]);
        $doing_app_id  = Redis::get($doing_app_key);

        // 整理出app状态：未做、正在做
        foreach ($now_apps as &$user_app) {
            if ($doing_app_id && $user_app->id == $doing_app_id) {
                $user_app->is_doing = true;
                continue;
            }

            $user_app->is_doing = false;
        }

        Util::die_json(compact('now_apps'));
    }
}
