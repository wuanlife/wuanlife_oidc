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
                throw new \Exception('用户信息不存在', 400);
            }
            return response([
                'id' => $user['id'],
                'avatar_url' => $user->avatar()->where('delete_flg', 0)->first()->url ?? env('AVATAR_URL'),
                'name' => $user->name,
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 500) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    /**
     * 根据用户 email ，获取 id （内部接口）
     * @param $email
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEmailById($email){
        try {
            $user = UsersBase::where('email',$email)->first();
            if (!$user) {
                throw new \Exception('用户信息不存在', 400);
            }
            return response([
                'id' => $user['id'],
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 500) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
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
        try {
            DB::transaction(function () use ($sub_point, $id) {
                WuanPoints::find($id)->increment('point', $sub_point);
                PointsOrder::create([
                    'user_id' => $id,
                    'points_alert' => $sub_point,
                ]);
            });

            return response([], 204);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 500) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }

    /**
     * 获取积分(内部接口)
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUserPoint($id, Request $request)
    {
        try {
            $user = WuanPoints::find($id);

            return response([
                'id' => $user['user_id'],
                'point' => $user['points']
            ], 200);
        } catch (\Exception $exception) {
            if ($exception->getCode() <= 300 || $exception->getCode() > 500) {
                return response(['error' => $exception->getMessage()], 400);
            } else {
                return response(['error' => $exception->getMessage()], $exception->getCode());
            }
        }
    }
}
