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

// 接口

$router->group(['middleware' => [], 'namespace' => 'Appleid', 'prefix' => 'appleid'], function () use ($router) {
    $router->get('/task/gettask', 'TaskController@get');
    $router->get('/task/report', 'TaskController@report');
    $router->get('/task/getverifycode', 'TaskController@getverifycode');
    $router->get('/task/getproxy', 'TaskController@getproxy');
    $router->get('/task/getproxy2', 'TaskController@getproxy2');
    $router->get('/task/query_success', 'TaskController@querySuccess');
});

// 后台
Route::group([
    'middleware' => ['cors'],
    'namespace'  => 'Backend',
    'prefix'     => 'backend',
], function ($router) {
    $router->post('/auth/login', 'AuthController@login');
    $router->get('/email/state_import', 'EmailController@stateImport');

$router->post('/appleid/import', 'AppleidController@import');
$router->get('/email/process_num', 'AppleidController@process_num');
$router->get('/appleid/get_today_num', 'AppleidController@getTodayNum');
    
});

Route::group([
    'middleware' => ['cors', 'auth:backend'],
    'namespace'  => 'Backend',
    'prefix'     => 'backend',
], function ($router) {
    $router->get('/auth/logout', 'AuthController@logout');
    $router->get('/auth/refresh', 'AuthController@refresh');
    $router->get('/auth/me', 'AuthController@me');



});
