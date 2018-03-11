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

// 验证Token完整性接口
Route::get('/auth','AuthController@verifyToken');

// 申请授权接口
Route::post('/auth','AuthController@getAccessToken');

// 登录接口
Route::post('/users/login','UsersController@login');

// 注册接口
Route::post('/users/register','UsersController@register');

// 获取用户信息
Route::get('/users/{id}','UsersController@getUserInfo');

// 退出登录接口
Route::post('/users/logout','UsersController@logout');

