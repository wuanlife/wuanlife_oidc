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
    'middleware' => 'check_id_token',
], function (){
    // 验证Token完整性接口
    Route::get('/auth','AuthController@verifyToken')->middleware('check_access_token');
    // 申请授权接口
    Route::post('/auth','AuthController@getAccessToken');
});


Route::group([
    'middleware' => [
        'check_id_token',
        'check_access_token',
    ]
],function () {
    // 获取用户信息接口
    Route::get('/users/{id}','UsersController@getUserInfo');
    // 修改用户信息接口
    Route::put('/users/{id}','UsersController@editorUserInfo');
});


Route::group([

], function (){
    // 登录接口
    Route::post('/users/login','UsersController@login');
    // 注册接口
    Route::post('/users/register','UsersController@register');
    // 退出登录接口
    Route::post('/users/logout','UsersController@logout');
    // 对午安应用返回用户信息接口
    Route::get('/app/users/{id}','UsersController@responseUserInfoToApp');
});

