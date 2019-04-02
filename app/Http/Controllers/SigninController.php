<?php

namespace App\Http\Controllers;

use App\Models\Fruits\WuanFruitLog;
use App\Models\Fruits\WuanSign;
use App\Models\Fruits\WuanFruit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

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
                    ->first();


            //获取今日0点的时间戳
            $nowtime = strtotime(date("Y-m-d", time()));

            //获取sign表中的时间转换位时间戳。
            $time = strtotime($sign['created_at']);

            if($sign==null){ //签到表中没有数据，刚注册的用户，未签到。
                $is_sign = 0;
            } else{
                //判断今日是否签到 （今日0点时间大于sign中的时间，表示为签到，反之，已签到。）
                $is_sign = $nowtime>$time ? 0 : 1;
            }



            return response([
                'range_min' => $range_min ,
                'range_max' => $range_max,
                'is_sign'   => $is_sign,

            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => "非法请求"], 400);
        }
    }




    /**
     * 签到
     * @param $user_id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sign($user_id, Request $request)
    {
        try {
//            if ($request->get('id-token')->uid != $user_id) {
//                throw new \Exception('Illegal request,user id does not match the token id.');
//            }



            $sign = WuanSign::where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->first();


            //获取今日0点的时间戳
            $nowtime = strtotime(date("Y-m-d", time()));

            //获取sign表中的时间转换位时间戳。
            $time = strtotime($sign['created_at']);

            if($sign==null){ //签到表中没有数据，刚注册的用户，未签到。
                $is_sign = 0;
            } else{
                //判断今日是否签到 （今日0点时间大于sign中的时间，表示为签到，反之，已签到。）
                $is_sign = $nowtime>$time ? 0 : 1;
            }


            $range_min = config ('signin.minNum');
            $range_max = config ('signin.maxNum');



            if($is_sign==0){
                $value = rand($range_min, $range_max);

                //开启事务
                DB::transaction(function() use ($value, $user_id){
                    $user_sign_info = ['user_id' => $user_id, 'value'=>$value, 'created_at'=>date('Y-m-d H:i:s' )];
                    WuanSign::create($user_sign_info);

                    //更新我的午安果数量
                    WuanFruit::find($user_id)->increment('value', $value);

                    // 新增获取记录
                    $new_log_info = [
                        'scene' => 2,
                        'user_id' => $user_id,
                        'value' => $value,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    WuanFruitLog::create($new_log_info);

                });


                
            } else {
                return response(['error' => '今日已签到'], 400);
            }



            return response([
                'user_id'    => $user_id,
                'value'      => $value
            ], 200);


        } catch (\Exception $exception) {
            return response(['error' => '今日已签到'], 400);
        }
    }
}
