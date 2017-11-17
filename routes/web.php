<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->group(['middleware' => [], 'namespace' => 'Jishua', 'prefix' => 'jishua'], function () use ($router) {
    $router->get('/task/get', 'TaskController@get');
    $router->get('/task/report', 'TaskController@report');
    $router->get('/task/invalid_account', 'TaskController@invalid_account');

    $router->get('/task/start', 'TaskController@start');
    $router->get('/task/stop', 'TaskController@stop');
    $router->get('/task/dispatch_mobile', 'TaskController@dispatchMobile');
    $router->get('/task/set_loop_id', 'TaskController@setLoopId');
});

Route::group([
    'middleware' => ['cors'],
    'namespace'  => 'Backend',
    'prefix'     => 'backend',
], function ($router) {
    $router->post('/auth/login', 'AuthController@login');
});

Route::group([
    'middleware' => ['cors', 'auth:backend'],
    'namespace'  => 'Backend',
    'prefix'     => 'backend',
], function ($router) {
    $router->get('/auth/logout', 'AuthController@logout');
    $router->get('/auth/refresh', 'AuthController@refresh');
    $router->get('/auth/me', 'AuthController@me');

    $router->get('/task/query', 'TaskController@query');
    $router->post('/task/save', 'TaskController@save');
    $router->get('/task/getFreeMobileNum', 'TaskController@getFreeMobileNum');
    $router->post('/task/saveTaskKeyword', 'TaskController@saveTaskKeyword');

    $router->get('/app/query_one', 'AppController@queryOne');
});

// $app->get('/login', function (Request $request) {
//     $token = app('auth')->attempt($request->only('username', 'password'));

//     return response()->json(compact('token'));
// });

// $app->get('/me', function (Request $request) {
//     return $request->user();
// });
