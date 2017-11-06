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
    }

}
