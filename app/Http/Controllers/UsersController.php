<?php
/**
 * Created by PhpStorm.
 * User: tacer
 * Date: 2018/3/11
 * Time: 20:52
 */

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    public function register()
    {

    }

    public function login(Request $request)
    {
        try {
            // 判断参数完整性
            if (!$request->filled(
                [
                    'email',
                    'password',
                    'redirect_uri',
                    'nonce',
                    'aud'
                ])
            ) {

                return \response(['error' => '缺少必要的参数']);
            }

            // 判断用户名和密码是否正确
            $email = $request->post('email');
            $user = User::select(['id','name', 'email', 'password'])
                ->where('email', '=', $email)
                ->first();
            if (!$user) {

                return \response(['error' => '用户不存在'], 400);
            }
            if ($user->password != md5($request->post('password'))) {

                return \response(['error' => '密码错误'], 400);
            }

            // 生成JWT-Token
            $token = JwtVerifier::makeIdToken(
                [
                    'uid' => $user->id,
                    'uname' => $user->name,
                    'email' => $user->email,
                    'iss' => 'wuan_oidc',
                    'sub' => $user->email,
                    'aud' => $request->get('aud'),
                    'nonce' => $request->get('nonce'),
                ]
            );

            return redirect()->away(
                $request->get('redirect_uri') . '?ID-Token=' . $token
            );
        } catch (\Exception $exception) {

            return response(['error' => $exception->getMessage()]);
        }
    }

    public function getUserInfo($id)
    {

    }

    public function logout()
    {

    }

}