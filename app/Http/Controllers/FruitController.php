<?php

namespace App\Http\Controllers;

use App\Models\Fruits\WuanFruit;
use Illuminate\Http\Request;

class FruitController extends Controller
{

    /**
     * 获取午安果数量
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function fruitsGet($id, Request $request)
    {
        try {
            if ($request->get('id-token')->uid != $id) {
                throw new \Exception('Illegal request,user id does not match the token id.');
            }
            $user = WuanFruit::find($id);

            return response([
                'id' => $user['user_id'],
                'value' => $user['value']
            ], 200);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
    }




}
