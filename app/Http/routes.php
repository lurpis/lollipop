<?php

$app->get('test', 'UserController@test');

$app->group(['prefix' => 'api', 'namespace' => 'App\Http\Controllers', 'middleware' => 'rate:60,3'], function () use ($app) {
    $app->get('key', 'KeyController@show');

});

$app->group(['prefix' => 'api', 'namespace' => 'App\Http\Controllers'], function () use ($app) {
    $app->get('user', 'UserController@show');
    $app->get('buy', 'OrderController@buy');
    $app->get('buy/url', 'OrderController@buyUrl');
    $app->get('token', 'UserController@token');
    $app->get('invite', 'WechatController@invite');
    $app->get('reward', 'WechatController@reward');
    $app->get('stats', 'UserController@stats');
    $app->get('login/{channel}', 'UserController@login');
    $app->get('retrieve/{chargeId}', 'OrderController@retrieve');
    $app->post('user', 'UserController@store');
    $app->post('webhook', 'OrderController@webHook');
    $app->post('taobao', 'TaobaoController@webHook');
    $app->get('test', 'UserController@test');
});

$app->group(['prefix' => 'admin/api', 'middleware' => 'auth', 'namespace' => 'App\Http\Controllers'], function () use ($app) {
    $app->get('user/list', 'UserController@index');
    $app->get('key/list', 'KeyController@index');
    $app->get('create/key', 'KeyController@create');
    $app->get('wechat/menu', 'WechatController@freshMenu');
    $app->post('key', 'KeyController@store');
    $app->get('send/{openid}/{amount}', 'UserController@send');
});

$app->group(['middleware' => 'auth', 'namespace' => '\Rap2hpoutre\LaravelLogViewer'], function () use ($app) {
    $app->get('admin/api/logs', 'LogViewerController@index');
});

$app->group(['prefix' => 'game'], function() use ($app) {
    $app->get('/2048', function() {
        return view('game/2048');
    });

    $app->get('/plane', function() {
        return view('game/plane');
    });

    $app->get('/catch', function() {
        return view('game/catch');
    });
});

$app->get('/qrcode', function() {
    return view('qrcode');
});

$app->get('wx/api', 'WechatController@index');
$app->post('wx/api', 'WechatController@index');
$app->put('wx/api', 'WechatController@index');

$app->get('/{any:.*}', function () {
    return view('index');
});