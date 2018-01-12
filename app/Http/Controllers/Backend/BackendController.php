<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BackendController extends Controller
{
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    public function where_view($where, $where_type = 1)
    {
        $user_id = $this->guard()->user()->id;
        if ($user_id != 10) {
            if ($where_type == 1) {
                $where['user_id'] = $user_id;
            } else {
                $where[] = ['user_id', '=', $user_id];
            }
        }
        return $where;
    }
}
