<?php

namespace App\Http\Controllers;

use App\Models\Fruits\WuanSign;
use App\Models\Fruits\WuanFruit;
use Illuminate\Http\Request;

class SigninController extends Controller
{
    /**
     * 获取签到规则及当日签到状态
     * @param $user_id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function signStatus($user_id, Request $request)
    {
        try {
//            if ($request->get('id-token')->uid != $user_id) {
//                throw new \Exception('Illegal request,user id does not match the token id.');
//            }
            $range_min = config ('signin.minNum');
            $range_max = config ('signin.maxNum');

            $sign = WuanSign::where('user_id', $user_id)
                    ->orderBy('created_at', 'desc')
                    ->get();

            dd($sign);

//            $sign_sort = $sign->sortByDesc('created_at');

//            dd($sign_sort);
//            if($sign==null){
//                throw new \Exception('非法用户');
//            }

            //获取今日0点的时间戳
            $nowtime = strtotime(date("Y-m-d", time()));

            //获取sign表中的时间转换位时间戳。
            $time = strtotime($sign['created_at']);

            //判断今日是否签到 （今日0点时间大于sign中的时间，表示为签到，反之，已签到。）
            $is_sign = $nowtime>$time ? 0 : 1;

            return response([
                'range_min' => $range_min ,
                'range_max' => $range_max,
                'is_sign'   => $is_sign,

            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => "非法请求"], 400);
        }
    }


    public function sign($user_id, Request $request)
    {
        try {
//            if ($request->get('id-token')->uid != $user_id) {
//                throw new \Exception('Illegal request,user id does not match the token id.');
//            }

            $user_id = WuanFruit::find($user_id);
            $sign = WuanSign::findAll($user_id);

            dd($sign);

            if($user_id==null || $sign==null){
                throw new \Exception('非法用户');
            }

            //获取今日0点的时间戳
            $nowtime = strtotime(date("Y-m-d"));

            //获取sign表中的时间转换位时间戳。
            $time = strtotime($sign['created_at']);

            //判断今日是否签到 （今日0点时间大于sign中的时间，表示为签到，反之，已签到。）
            $is_sign = $nowtime>$time ? 0 : 1;


            return response([
                'user_id' => $user_id['user_id'],
                'value' => $user_id['value'],
                'is_sign' => $is_sign,
            ], 200);


        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }
}
