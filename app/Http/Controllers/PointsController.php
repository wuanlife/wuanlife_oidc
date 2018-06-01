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
        $list = AppPointDetail::all();
        $res = [];
        $param = Builder::getParam();
        foreach ($list as $item) {
            try {
                $client = new Client(['base_uri' => $item->address]);
                $response = $client->request('GET', "api/app/users/{$id}/points", [
                        'headers' => [
                            'ID-Token' => $request->header('ID-Token'),
                            'Access-Token' => $request->header('Access-Token'),
                        ],
                        'json' => $param,
                    ]
                );
            } catch (\Exception $e) {
                return response(['error' => $e->getMessage()]);
            }
            $json = $response->getBody()->getContents();
            $points = \json_decode($json)->points;
            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'exchange_rate' => $item->exchange_rate,
                'points' => $points,
            ];
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

        // 获取子系统地址和汇率
        $url = $app->address;
        $exchange_rate = $app->exchange_rate;
        $param = Builder::getParam([
            'sub_points' => $sub_point * $exchange_rate,
            'action' => 'decrement',
        ]);

        DB::beginTransaction();
        try {
            $client = new Client(['base_uri' => $url]);
            $response = $client->request('PUT', "api/app/users/{$id}/points", [
                    'headers' => [
                        'ID-Token' => $request->header('ID-Token'),
                        'Access-Token' => $request->header('Access-Token'),
                    ],
                    'json' => $param,
                ]
            );
            if ($response->getStatusCode() == 204) {
                WuanPoints::find($id)->increment('points',$sub_point);
            } else {
                throw new \Exception('Fail to exchange points');
            }

            DB::commit();
            return response([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => $e->getMessage()], 400);
        }

    }
}
