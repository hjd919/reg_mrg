<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

//require "../vendor/xiaosier/libweibo/saetv2.ex.class.php";

class WeiboController extends Controller
{
    public function share(
        User $userModel,
        Request $request
    ) {
        $c = new \SaeTClientV2('794786010', 'a4876ec7031ab42408233b9f9a3110bc', '2.00fNFW1C0MJqmr5ddc571e57Q14QfC');
        // 待发送的文字内容
        $status = 'test';
        // 本地一张图片，也可以不带图片
        // 拼接'http://weibosdk.sinaapp.com/'是因为这个share接口至少要带上一个【安全域名】下的链接。
        $ret = $c->share($status . 'http://weibosdk.sinaapp.com/');
        var_dump($ret);
    }
}
