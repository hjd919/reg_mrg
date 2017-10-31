<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Util;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function weibo()
    {
        return Socialite::with('weibo')->stateless()->redirect();
    }

    public function weiboCallback(User $userModel)
    {
        $user = Socialite::with('weibo')->user();
        Log::info(var_export($user, true));

        // 跳转到首页 - 192.168.230.xxx
        return redirect($redirect_url);
    }

    // 微信登录
    public function wechat()
    {
        return Socialite::with('weixin')->stateless()->redirect();
    }

    public function wechatCallback(User $userModel)
    {
        $user = Socialite::with('weixin')->user();
        // Log::info('用户信息' . var_export($user, true));
        if (empty($user) || empty($user->user)) {
            return response()->json(['授权失败,请重试']);
        }
        $userinfo = $user->user;
        // Log::info('用户信息1' . var_export($userinfo, true));

        // 判断是否新用户
        $openid = $userinfo['openid'];
        $user   = $userModel->isNew($openid);
        if (!$user) {
            Log::info('新用户，添加用户');

            // 新用户，添加用户
            $userdata = [
                'user_name' => $userinfo['nickname'],
                'nickname'  => $userinfo['nickname'],
                'openid'    => $openid,
                'sex'       => $userinfo['sex'],
                'province'  => $userinfo['province'],
                'city'      => $userinfo['city'],
                'avatar'    => $userinfo['headimgurl'],
            ];
            $user = $userModel->createUser($userdata);
            // Log::info('新 $user');
            // Log::info(var_export($user, true));
        } else {
            // Log::info('旧 $user');
            // Log::info(var_export($user, true));
        }

        // 登录
        $token           = str_random(60);
        $user->api_token = $token;
        $res             = $user->save();
        // Log::info('登录-保存token:' . var_export($res, true));

        $redirect     = urlencode('/login?api_token=' . $token);
        $redirect_url = 'http://slsw.yz210.com?redirect=' . $redirect;
        // Log::info('登录-跳转地址:' . $redirect_url);
        // $token = JWTAuth::fromUser($user);

        // 跳转到首页 - 192.168.230.xxx
        return redirect($redirect_url);
    }

    public function phone()
    {
        $user      = User::find(11);
        $api_token = $user->api_token;
        Util::die_json(compact('api_token'));
    }
}
