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

    // A3 获取七牛 token 接口
    Route::get('/qiniu/token', 'QiNiuController@getUploadToken');
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

    // Q1 获取积分种类列表
    Route::get('/users/{id}/points_list', 'PointsController@getList')->where('id', '[0-9]+');
    // Q2 获取午安账号积分接口
    Route::get('/users/{id}/wuan_points', 'PointsController@accountsGet')->where('id', '[0-9]+');
    // Q3 兑换积分
    Route::put('/users/{id}/points', 'PointsController@exchange')->where('id', '[0-9]+');

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
    // U6 搜索用户
    Route::post('/users/search', 'UsersController@search');
});

// 内部通信接口
Route::group([
    'middleware' => [
        'requester_auth'
    ],
], function () {
    // 获取午安账号积分接口
    Route::get('/app/users/{id}/point', 'InteriorCommunication@getUserPoint')->where('id', '[0-9]+');
    // 兑换午安账号积分接口
    Route::put('/app/users/{id}/point', 'InteriorCommunication@putUserPoint')->where('id', '[0-9]+');
    // 根据用户 id 获取用户信息接口
    Route::get('/app/users/{id}', 'InteriorCommunication@responseUserInfoToApp')->where('id', '[0-9]+');
    // 通过用户 email 获取用户 id 接口
    Route::get('/app/users/email/{email}', 'InteriorCommunication@getEmailById')->where('id', '[0-9]+');
    // 搜索用户 接口
    Route::get('/app/users/search', 'InteriorCommunication@search');
});


// P1 找回密码接口(发送邮件)
Route::post('/email/{email}', 'PasswordController@requestToSend');
// P2 重置密码
Route::post('/users/{id}/password', 'PasswordController@resetPassword')->where('id', '[0-9]+');
// P3 修改密码接口
Route::put('/users/{id}/password', 'PasswordController@modify')->middleware('check_id_token')->where('id', '[0-9]+');
// P4 验证 Email Token 合法性接口
Route::post('/user/{id}/token_verify', 'PasswordController@tokenVerification')->where('id', '[0-9]+');

Route::get('/users/{id}/wuan_fruit', 'FruitController@fruitsGet')->where('id', '[0-9]+');

Route::get('/users/{id}/wuan_sign_info', 'SigninController@signStatus')->where('id', '[0-9]+');

Route::get('/users/{id}/wuan_sign', 'SigninController@sign')->where('id', '[0-9]+');