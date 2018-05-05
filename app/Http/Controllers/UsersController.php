<?php
/**
 * Created by PhpStorm.
 * User: tacer
 * Date: 2018/3/11
 * Time: 20:52
 */

namespace App\Http\Controllers;


use App\Models\Users\{
    Avatar, SexDetail, User, UserDetail, WuanScore
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    /**
     * 注册接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        try {
            // 验证参数完整性
            $validator = Validator::make($request->all(),
                [
                    'name' => 'bail|required|string|alpha_dash',
                    'email' => 'bail|required|string|email',
                    'password' => 'bail|required|string|between:6,20|alpha_dash',
                    'client_id' => 'required'

                ]);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 422);
            } elseif (User::where('email', '=', $request->post('email'))->first()) {
                throw new \Exception('该邮箱已注册', 400);
            } elseif (User::where('name', '=', $request->post('name'))->first()) {
                throw new \Exception('该用户名已被注册', 400);
            }

            // 注册用户信息
            $user = User::create([
                'name' => $request->post('name'),
                'email' => $request->post('email'),
                'password' => md5($request->post('password')),
            ]);
            UserDetail::create([
                'sex' => 'male',
                'birthday' => '1990-1-1'
            ]);
            Avatar::create([
                'user_id' => $user->id,
                'url' => env('AVATAR-URL', ' ')
            ]);
            if (!$user) {
                throw new \Exception('创建用户失败', 400);
            }

            $id_token = JwtVerifier::makeIdToken(
                [
                    'uid' => $user->id,
                    'uname' => $user->name,
                    'email' => $user->email,
                    'iss' => 'https://wuan.com',
                    'sub' => $user->id,
                    'aud' => $request->get('client_id')
                ]
            );

            // 注册成功，返回重定向信息
            return
                response(['ID-Token' => $id_token]);
//                    ->withCookie(
//                        Cookie::make('wuan-id-token', $id_token, 60 * 60 * 24 * 7)
//                    );

        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    /**
     * 登录接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request)
    {
        try {
            // 验证参数完整性
            $validator = Validator::make($request->all(),
                [
                    'email' => 'bail|required|string|email',
                    'password' => 'bail|required|string|between:6,20|alpha_dash',
                    'client_id' => 'required'

                ]);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 422);
            }

            // 判断用户名和密码是否正确
            $email = $request->post('email');
            $user = User::select(['id', 'name', 'email', 'password'])
                ->where('email', '=', $email)
                ->first();
            if (!$user) {

                throw new \Exception('用户不存在', 400);
            }
            if ($user->password != md5($request->post('password'))) {

                throw new \Exception('密码不正确', 400);
            }

            // 生成 JWT-Token
            $id_token = JwtVerifier::makeIdToken(
                [
                    'uid' => $user->id,
                    'uname' => $user->name,
                    'email' => $user->email,
                    'iss' => 'https://wuan.com',
                    'sub' => $user->id,
                    'aud' => $request->get('client_id')
                ]
            );

            // 登陆成功，设置 cookie 并返回重定向请求
            return
                response(['ID-Token' => $id_token]);
//                    ->withCookie(
//                        Cookie::make('wuan-id-token', $id_token, 60 * 60 * 24 * 7)
//                    );
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }
    public function getUserScore($id, Request $request)
    {
        $id_token = $request->get('id-token');
        $as_token = $request->get('access-token');
        try {
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            $user = WuanScore::find($id_token->uid);
            $scope = array_flip(explode(',', $as_token->scope));
            if (isset($scope['public_profile'])) {
                return response([
                    'id'    =>$user['user_id'],
                    'score' =>$user['score']
                ], 200);
            } else {
                return response([], 200);
            }
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }
    /**
     * 获取用户信息接口
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUserInfo($id, Request $request)
    {
        $id_token = $request->get('id-token');
        $as_token = $request->get('access-token');
        try {
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            $user = User::find($id);
            $scope = array_flip(explode(',', $as_token->scope));
            if (isset($scope['public_profile'])) {
                return response([
                    'id' => $user['id'],
                    'avatar_url' => $user->avatar()->where('delete_flg', 0)->first()->url ?? null,
                    'mail' => $user->email,
                    'name' => $user->name,
                    'sex' => $user->userDetail->sex ?? null,
                    'birthday' => $user->userDetail->birthday ?? null,
                ], 200);
            } else {
                return response([], 200);
            }
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    /**
     * 向午安应用服务器返回用户信息
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function responseUserInfoToApp($id)
    {
        try {
            //$token = $request->get('token');
            //$this->verifyAppToken($token);
            $user = User::find($id);
            if (!$user) {
                throw new \Exception('用户信息不存在', 400);
            }
            return response([
                'id' => $user['id'],
                'avatar_url' => $user->avatar()->where('delete_flg', 0)->first()->url ?? env('AVATAR_URL'),
                'name' => $user->name,
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    public function putUserScore($id, Request $request)
    {
        $id_token = $request->get('id-token');
        $sub_score = $request->get('sub_score');
        try{
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            DB::transaction(function () use ($sub_score, $id_token) {
                WuanScore::find($id_token->uid)->increment('score', $sub_score);
            });

        }
        catch (\Exception $exception){
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }
    /**
     * 修改用户信息接口
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function editorUserInfo($id, Request $request)
    {
        $id_token = $request->get('id-token');
        try {
            if ($id != $id_token->uid) {
                throw new \Exception('非法请求，用户ID与令牌ID不符', 400);
            }
            if (empty($request->only(
                [
                    'name',
                    'avatar_url',
                    'sex',
                    'birthday'
                ]))
            ) {
                throw new \Exception('没有要修改的内容', 400);
            }
            DB::beginTransaction();
            if (isset($request->name)) {
                if (User::where('name', '=', $request->post('name'))->first()) {
                    throw new \Exception('该用户名已被注册', 400);
                }
                User::where('id', '=', $id)->update(['name' => $request->name]);
            }
            if (isset($request->avatar_url)) {
                Avatar::where('user_id', $id)->where('delete_flg', 0)->update(['delete_flg' => '1']);
                Avatar::create(['user_id' => $id, 'url' => $request->avatar_url]);
            }
            if (isset($request->sex)) {
                if ($a = SexDetail::where('id', $request->sex)->first()) {
                    throw new \Exception('非法请求，错误的性别类型', 400);
                }
                UserDetail::where('id', '=', $id)->update(['sex' => $request->sex]);
            }
            if (isset($request->birthday)) {
                UserDetail::where('id', '=', $id)->update(['birthday' => $request->birthday]);
            }
            DB::commit();
            return response(['success' => '修改成功'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            if ($exception->getCode() <= 300 || $exception->getCode() > 510) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    /**
     * 退出登录接口
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        try {
            return response(['success' => '退出登录成功'], 200)
                ->withCookie(Cookie::forget('wuan-access-token'))
                ->withCookie(Cookie::forget('wuan-id-token'));
        } catch (\Exception $exception) {
            return response(['error' => '退出登录失败'], 400);
        }
    }


    /**
     * 验证用户的授权token
     * @param $token
     * @throws \Exception
     */
    private function verifyAppToken($token)
    {
        $key = env('WUAN_APP_KEY');
        $info = explode('.', $token);
        if (count($info) !== 2) {
            throw new \Exception('无效token', 400);
        }
        if (crypt(base64_decode($info[0]), $key) == base64_decode($info[1])) {
            throw new \Exception('无效token', 400);
        }
    }
}