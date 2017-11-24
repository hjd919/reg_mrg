<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{

    // 判断app更新接口
    public function update(
        Request $request
    ) {
        // func getdevice_id
        $get_device_id = function () {
            $ua        = $_SERVER['HTTP_USER_AGENT'];
            $data      = explode('device/', $ua);
            $device_id = empty($data[1]) ? '0' : $data[1];
            return $device_id;
        };
        $device_id = get_device_id();

        // * 根据version和device_id获取当前版本是否需要更新
        // 获取current_version,mobile_group_id
        $current_version = $request->input('current_version', 1000);
        $mobile_group_id = $request->input('mobile_group_id', 0);
        if (!$mobile_group_id || !$current_version) {
            Util::die_jishua("que shao can shu-{mobile_group_id:{$mobile_group_id},current_version:{$current_version}", 1);
        }
        // 根据group_id获取最新版本号
        $last_version = DB::table('config')->select('value')->where('name', 'last_version,mgi:' . $mobile_group_id)->value('value');
        // 对比是否是最新版本
        $is_update = false;
        $url       = '';
        if ($current_version < $last_version) {
            // 1.3.不是，则显示要更新，给出更新地址
            $url = DB::table('app_update')->select('url')
                ->where('version', $last_version)
                ->where('mobile_group_id', $mobile_group_id)
                ->value('url');
            $is_update = true;
        }

        Util::die_jishua([
            'url'       => $url,
            'is_update' => $is_update,
        ]);
    }

}
