<?php

namespace App\Http\Controllers;


use App\Models\Fruits\WuanFruit;
use Illuminate\Http\Request;
use App\Models\Points\WuanPoints;
use App\Models\Users\{
    Avatar, SexDetail, UsersBase, UserDetail
};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\{
    Cookie, DB, Validator
};


class UsersController extends Controller
{

    /**
     *注册接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        try {
            // 验证参数完整性
            $validator = Validator::make($request->all(),
                [
                    'name' => 'bail|required|string|regex:/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u',
                    'email' => 'bail|required|string|email',
                    'password' => 'bail|required|string|between:6,20',
                    'client_id' => 'required'

                ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            } elseif (UsersBase::where('email', '=', $request->post('email'))->first()) {
                return response(['error' => 'Email address already exists'], 400);
            } elseif (UsersBase::where('name', '=', $request->post('name'))->first()) {
                return response(['error' => 'Username already exists'], 400);
            }

            DB::beginTransaction();
            // 注册用户信息
            $user = UsersBase::create([
                'name' => $request->post('name'),
                'email' => $request->post('email'),
                'password' => md5($request->post('password')),
            ]);
            UserDetail::create([
                'sex' => 'secrecy',
                'birthday' => '1990-1-1'
            ]);
            Avatar::create([
                'user_id' => $user->id,
                'url' => env('AVATAR_URL', ' ')
            ]);
//            WuanPoints::create([
//                'user_id' => $user->id,
//                'points' => 0,
//            ]);
            WuanFruit::created([
                'user_id' => $user->id
            ]);
            DB::commit();
            if (!$user) {
                DB::rollBack();
                return response(['error' => 'Failed to register'], 400);
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
            return response(['ID-Token' => $id_token]);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
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
                    'password' => 'bail|required|string|between:6,20',
                    'client_id' => 'required'

                ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            // 判断用户名和密码是否正确
            $email = $request->post('email');
            $user = UsersBase::select(['id', 'name', 'email', 'password'])
                ->where('email', '=', $email)
                ->first();
            if (!$user) {

                return response(['error' => 'User does not exist'], 422);
            }
            if ($user->password != md5($request->post('password'))) {

                return response(['error' => 'Incorrect password'], 400);
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
            return response(['ID-Token' => $id_token]);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
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
                return response(['error' => 'Illegal request,user id do not match this token'], 403);
            }
            $user = UsersBase::find($id);
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
            return response(['error' => $exception->getMessage()], 400);
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
                throw new \Exception('Illegal request,user id does not match the token id', 400);
            }
            if (empty($request->only(
                [
                    'name',
                    'avatar_url',
                    'sex',
                    'birthday'
                ]))
            ) {
                return response(['error' => 'Nothing to change'], 400);
            }
            DB::beginTransaction();
            if (isset($request->name)) {
                if (UsersBase::where('name', '=', $request->post('name'))->first()) {
                    return response(['error' => 'Username already exists'], 400);
                }
                UsersBase::where('id', '=', $id)->update(['name' => $request->name]);
            }
            if (isset($request->avatar_url)) {
                Avatar::where('user_id', $id)->where('delete_flg', 0)->update(['delete_flg' => '1']);
                Avatar::create(['user_id' => $id, 'url' => $request->avatar_url]);
            }
            if (isset($request->sex)) {
                if (!SexDetail::where('id', $request->sex)->first()) {
                    return response(['error' => 'Illegal request,error type of sex'], 422);
                }
                UserDetail::where('id', '=', $id)->update(['sex' => $request->sex]);
            }
            if (isset($request->birthday)) {
                UserDetail::where('id', '=', $id)->update(['birthday' => $request->birthday]);
            }
            DB::commit();
            return response(['success' => 'Successfully modified'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 退出登录接口
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        try {
            return response(['success' => 'Logout successful'], 200)
                ->withCookie(Cookie::forget('wuan-access-token'))
                ->withCookie(Cookie::forget('wuan-id-token'));
        } catch (\Exception $exception) {
            return response(['error' => 'Failed to logout'], 400);
        }
    }

    /**
     * U6 搜索用户
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword') ?? '';
        if (!$keyword) {
            return response(['users' => [], 'total' => 0], Response::HTTP_OK);
        }
        $limit = $request->input('limit') ?? 10;
        //每页显示数
        $offset = $request->input('offset') ?? 0;   //每页起始数
        $page = ($offset / $limit) + 1;
        try {
            $users = UsersBase::where('name', 'like', "%$keyword%")->paginate($limit, ['*'], '', $page);
            if ($users->isEmpty()) {
                return response(['users' => [], 'total' => 0], Response::HTTP_OK);
            }
            foreach($users as $user) {
                $res['users'][] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar()->where('delete_flg', 0)->first()->url ?? null,
                ];
            }
            $res['total'] = $users->total();
            return response($res, 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

}