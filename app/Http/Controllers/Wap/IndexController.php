<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use App\Models\Channel;
use App\Models\Feedback;
use App\Models\Menu;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    // 资讯
    public function get(
        Request $request,
        Menu $menuModel,
        Carousel $carouselModel,
        Channel $channelModel
    ) {
        $channels = $channelModel->getList();

        $menus = $menuModel->getList();

        $carousels = $carouselModel->getList();

        Util::die_json(compact('channels', 'menus', 'carousels'));
    }

    public function submitFeedback(
        Request $request
    ) {
        $user = Auth::user();

        $feedback = $request->feedback;
        if (!$feedback) {
            Util::die_json('请填写你的意见反馈', 1);
        }
        $res = Feedback::create([
            'user_id'  => $user->id,
            'feedback' => $feedback,
        ]);
        if ($res) {
            Util::die_json('感谢您的反馈！');
        }
    }
}
