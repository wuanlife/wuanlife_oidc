<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', function () {
    return view('index');
});

Route::get('/authorize', function () {
    return view('index');
});

Route::get('/signup', function () {
    return view('index');
});

Route::post('/api/email',"ResetPassword@sendEmail");//申请通过邮箱找回密码
Route::post('/api/users/{id}/password',"ResetPassword@resetPassword");//重置密码
Route::post('/api/auth/password',"ResetPassword@tokenVerification");//验证token是否过期
Route::put('/api/users/{id}/password','ResetPassword@modifyPassword')->
    middleware('check_id_token');//修改密码
