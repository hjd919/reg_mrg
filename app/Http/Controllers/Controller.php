<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function success_response($data = [])
    {
        $data['ret'] = 0;
        return response()->json($data);
    }

    protected function fail_response($data = [])
    {
        $data['ret'] = 1;
        return response()->json($data);
    }
}
