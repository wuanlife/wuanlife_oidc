<?php
/**
 * Created by PhpStorm.
 * User: tacer
 * Date: 2018/3/11
 * Time: 20:53
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class AuthController extends Controller
{

    public const ACCESS_TOKEN_KEY = 'Access_Token';
    public const ID_TOKEN_KEY = 'ID_Token';

    /**
     * 验证Token合法性
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyToken(Request $request)
    {
        try {
            if ($access_token = $request->header(AuthController::ACCESS_TOKEN_KEY)) {
                JwtVerifier::verifyToken($access_token, 'Access');
            }
            if ($id_token = $request->header(AuthController::ID_TOKEN_KEY)) {
                JwtVerifier::verifyToken($id_token, 'ID');
            }
            return \response(['success' => '验证成功'], 200);
        } catch (\Exception $exception) {
            return \response(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * 申请授权接口（获取Access-Token）
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAccessToken(Request $request)
    {
        try {
            if (!$request->filled(JwtVerifier::ACCESS_REQUEST_PARAMS)) {
                throw new \Exception('缺少必要的参数', 400);
            }

            $access_token = JwtVerifier::makeAccessToken(
                $request->only(JwtVerifier::ACCESS_REQUEST_PARAMS),
                60 * 60 * 24 * 7
            );

            return response(['Access-Token' => $access_token]);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], $exception->getCode());
        }

    }


}