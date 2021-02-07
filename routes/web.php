<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
$router->get('/start', 'Index@start');

$router->get('/', [
    'as' => 'index',
    'uses' => 'Index@index'
]);

$router->group(['prefix' => 'oauth'], function () use ($router) {
    $router->get('/wechat', [
        'as' => 'oauth.wechat',
        'uses' => 'OAuth@wechat'
    ]);
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/base', 'Index@base');

    $router->group(['prefix' => 'order'], function () use ($router) {
        $router->post('/create', 'Order@create');
        $router->post('/pay', 'Order@pay');
        $router->get('/lists', 'Order@lists');
        $router->delete('/{no}', 'Order@delete');
    });
});

$router->group(['prefix' => 'notify'], function () use ($router) {
    $router->post('/wechat', [
        'as' => 'notify.wechat',
        'uses' => 'Notify@wechat'
    ]);
    $router->post('/alipay', [
        'as' => 'notify.alipay',
        'uses' => 'Notify@alipay'
    ]);
});
