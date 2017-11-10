<?php
/*
 * Author:xx_lufei
 * Time:2016年11月3日13:15:51
 * Note:Access Control Headers.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HandleOptionsMiddleware
{
    private $headers;
    private $allow_origin;

    public function handle(Request $request, \Closure $next)
    {
        // [参考](http://www.jianshu.com/p/ce6a14a4a270)
        // $this->allow_origin = [
        //     'http://localhost:8000',
        //     'http://192.168.1.12:8080',
        // ];
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // //如果origin不在允许列表内，直接返回403
        // if (!in_array($origin, $this->allow_origin) && !empty($origin)) {
        //     return new Response('Forbidden', 403);
        // }

        //如果是复杂请求，先返回一个200，并allow该origin
        if ($request->isMethod('options')) {
            $this->headers = [
                'Access-Control-Allow-Methods'     => 'GET, POST',
                'Access-Control-Allow-Headers'     => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true', //允许客户端发送cookie
                'Access-Control-Max-Age'           => 1728000, //该字段可选，用来指定本次预检请求的有效期，在此期间，不用发出另一条预检请求。
            ];
            return $this->setCorsHeaders(new Response('OK', 200), $origin);
        } else {
            return $next($request);
        }
    }

    /**
     * @param $response
     * @return mixed
     */
    public function setCorsHeaders($response, $origin)
    {
        $response->header('Access-Control-Allow-Origin', $origin);
        foreach ($this->headers as $key => $value) {
            $response->header($key, $value);
        }
        // if (in_array($origin, $this->allow_origin)) {
        //     $response->header('Access-Control-Allow-Origin', $origin);
        // } else {
        //     $response->header('Access-Control-Allow-Origin', '');
        // }
        return $response;
    }
}
