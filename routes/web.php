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
});
