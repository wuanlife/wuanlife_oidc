<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => [
        'check_id_token',
    ],
], function () {
    // A2 申请授权接口
    Route::post('/auth', 'AuthController@getAccessToken');
});


Route::group([
    'middleware' => [
        'check_id_token',
        'check_access_token',
    ]
], function () {
    // U3 获取用户信息接口
    Route::get('/users/{id}', 'UsersController@getUserInfo')->where('id', '[0-9]+');
    // U5 修改用户信息接口
    Route::put('/users/{id}', 'UsersController@editorUserInfo')->where('id', '[0-9]+');
});


Route::group([

], function () {
    // A1 验证Token完整性接口
    Route::get('/auth', 'AuthController@verifyToken');
    // U1 登录接口
    Route::post('/users/login', 'UsersController@login');
    // U2 注册接口
    Route::post('/users/register', 'UsersController@register');
    // U4 退出登录接口
    Route::post('/users/logout', 'UsersController@logout');
});

// 内部通信接口
Route::group([
    'middleware' => [
        'requester_auth',
        'check_id_token',
        'check_access_token',
    ],
], function () {
    // 获取午安账号积分接口
    Route::get('/app/users/{id}/point', 'UsersController@getUserPoint')->where('id', '[0-9]+');
    // 兑换午安账号积分接口
    Route::put('/app/users/{id}/point', 'UsersController@putUserPoint')->where('id', '[0-9]+');

});

// U6 获取用户信息接口
Route::get('/app/users/{id}', 'UsersController@responseUserInfoToApp')->where('id', '[0-9]+');