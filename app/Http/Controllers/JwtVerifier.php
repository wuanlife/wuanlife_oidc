<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Mockery\Exception;

class JwtVerifier extends Controller
{
    public const JWT_KEY = 'wuan_life_liudagejianghualitaipian';
    public const ALGORITHMS = ['HS256'];
    private const ACCESS_TOKEN_PARAMS =
        [
            'response_type',
            'client_id',
            'state',
            'scope',
            'exp',
            'iat',
        ];
    private const ID_TOKEN_PARAMS =
        [
            'uid',
            'uname',
            'email',
            'iss',
            'sub',
            'aud',
            'exp',
            'iat',
            'auth_time',
            'nonce',
        ];

    /**
     * 验证 Token 合法性
     * @param $jwt
     * @param $type
     * @throws \Exception
     */
    public static function verifyToken($jwt, $type)
    {
        $data = JWT::decode($jwt, self::JWT_KEY, self::ALGORITHMS);
        switch ($type) {
            case 'Access':
                $params = self::ACCESS_TOKEN_PARAMS;
                break;
            case 'ID':
                $params = self::ID_TOKEN_PARAMS;
                break;
            default:
                throw new \Exception('错误的Token类型');
        }
        foreach ($params as $item) {
            if (empty($data->$item)) {
                throw new \Exception($type . '-Token完整性验证失败');
            }
        }
        self::verifyExp($data->exp);
    }

    /**
     * 生成 Access-Token
     * @param array $data
     * @param int $exp
     * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public static function makeAccessToken(array $data, $exp = 604800)
    {
        $data['iat'] = time();
        $data['exp'] = time() + $exp;
        $params = self::ACCESS_TOKEN_PARAMS;
        foreach ($params as $item) {
            if (empty($data[$item])){
                throw new \Exception('缺少必要项：' . $item);
            }
            $params[$item] = $data[$item];
        }
        try {
            return JWT::encode($params, self::JWT_KEY, 'HS256');
        } catch (\Exception $exception) {
            return response(['error' => '生成Access-Token失败'], 400);
        }
    }

    /**
     * 生成 ID-Token
     * @param array $data
     * @param int $exp
     * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public static function makeIdToken(array $data, $exp = 604800)
    {
        $data['auth_time'] = $data['iat'] = time();
        $data['exp'] = time() + $exp;

        $params = self::ID_TOKEN_PARAMS;
        foreach ($params as $item) {
            if (empty($data[$item])){
                throw new \Exception('缺少必要项：' . $item);
            }
            $params[$item] = $data[$item];
        }
        try {
            return JWT::encode($params, self::JWT_KEY, 'HS256');
        } catch (\Exception $exception) {
            return response(['error' => '生成Access-Token失败'], 400);
        }
    }

    /**
     * 验证 JWT 是否过期
     * @param $exp
     */
    private static function verifyExp($exp)
    {
        if (time() > $exp){
            throw new Exception('Token已过期，请重新获取');
        }
    }

}
