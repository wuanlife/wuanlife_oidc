<?php

namespace App\Http\Controllers;

use App\Models\Points\AppPointDetail;
use App\Models\Points\WuanPoints;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PointsController extends Controller
{
    /**
     * 获取应用积分兑换系统详情
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws GuzzleException
     */
    public function getList($id, Request $request)
    {
        try {
            if ($request->get('id-token')->uid != $id) {
                throw new \Exception('Illegal request,user id does not match the token id.');
            }
            $list = AppPointDetail::all();
            $res = [];
            foreach ($list as $item) {
                $response = Builder::requestInnerApi(
                    $item->address,
                    "/api/app/users/{$id}/points", 'GET',
                    [
                        'ID-Token' => $request->header('ID-Token'),
                        'Access-Token' => $request->header('Access-Token'),
                    ]);


                $json = $response['contents'];
                $points = \json_decode($json)->points;
                $res[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'exchange_rate' => $item->exchange_rate,
                    'points' => $points,
                ];
            }
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()]);
        }
        return response(['app' => $res], 200);
    }

    /**
     * 获取用户午安积分
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function accountsGet($id, Request $request)
    {
        try {
            if ($request->get('id-token')->uid != $id) {
                throw new \Exception('Illegal request,user id does not match the token id.');
            }
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
     * 兑换积分
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws GuzzleException
     */
    public function exchange($id, Request $request)
    {
        // 验证参数完整性
        $validator = Validator::make($request->all(),
            [
                'sub_app' => 'required',
                'sub_points' => 'required',
            ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        // 验证兑换的数额是否为正整数
        $sub_point = $request->input('sub_points');
        if (!is_numeric($sub_point) || ($sub_point < 0) || (floor($sub_point) != $sub_point)) {
            return response(['error' => 'Illegal request,sub_point must be a integer number'], 422);
        }

        // 验证要兑换的系统是否存在积分系统
        $app = AppPointDetail::find($request->input('sub_app'));
        if (!$app) {
            return response(['error' => 'Illegal request,the requested app does not have point system.'], 404);
        }

        DB::beginTransaction();
        try {
            WuanPoints::find($id)->increment('points', $sub_point);

            $response = Builder::requestInnerApi(
                $app->address,
                "/api/app/users/{$id}/points",
                'PUT',
                [
                    'ID-Token' => $request->header('ID-Token'),
                    'Access-Token' => $request->header('Access-Token'),
                ],
                [
                    'sub_points' => $sub_point * $app->exchange_rate,
                    'action' => 'decrement',
                ]
            );
            if ($response['status_code'] != 204) {
                throw new \Exception('Fail to exchange points:' . $response->getBody()->getContents());
            }

            DB::commit();
            return response([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => $e->getMessage()], 400);
        }

    }
}
