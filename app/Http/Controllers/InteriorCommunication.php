<?php

namespace App\Http\Controllers;

use App\Models\Points\PointsOrder;
use App\Models\Points\WuanPoints;
use App\Models\Users\UsersBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteriorCommunication extends Controller
{
    /**
     * 向午安应用服务器返回用户信息(内部接口)
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function responseUserInfoToApp($id)
    {
        try {
            $user = UsersBase::find($id);
            if (!$user) {
                return response(['error' => 'User info not found'], 404);
            }
            return response([
                'id' => $user['id'],
                'avatar_url' => $user->avatar()->where('delete_flg', 0)->first()->url ?? env('AVATAR_URL'),
                'name' => $user->name,
            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 根据用户 email ，获取 id （内部接口）
     * @param $email
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEmailById($email)
    {
        try {
            $user = UsersBase::where('email', $email)->first();
            if (!$user) {
                return response(['error' => 'User info not found'], 404);
            }
            return response([
                'id' => $user['id'],
            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 增加用户积分(内部接口)
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function putUserPoint($id, Request $request)
    {
        $sub_point = $request->input('sub_point');
        if (!is_numeric($sub_point) || ($sub_point < 0) || (floor($sub_point) != $sub_point)) {
            return response(['error' => 'point must be integer'], 422);
        }
        try {
            DB::transaction(function () use ($sub_point, $id) {
                WuanPoints::find($id)->increment('points', $sub_point);
                PointsOrder::create([
                    'user_id' => $id,
                    'points_alert' => $sub_point,
                ]);
            });

            return response([], 204);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 获取积分(内部接口)
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUserPoint($id)
    {
        try {
            $user = WuanPoints::find($id);
            return response([
                'id' => $user['user_id'],
                'points' => $user['points']
            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 搜索用户
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function search(Request $request)
    {
        $limit =  $request->input('limit') ?? 20;
        $offset = $request->input('offset') ?? 0;
        $keyword = $request->input('keyword');

        try {
            $users = UsersBase::where('name', 'like', '%' . $keyword . '%')->paginate($limit, ['*'] ,'' , $offset);
            if (!$users) {
                return response(['users' => []], 200);
            }

            $res = [];
            foreach ($users as $user) {
                $res[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar ? $user->avatar->url : '',
                ];
            }
            return response(['users' => $res]);
        } catch (\Exception $exception) {
            return response(['error' => '搜索失败'], 400);
        }
    }
}
