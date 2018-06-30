<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Mockery\Exception;

class JwtVerifier extends Controller
{
    public const ALGORITHMS = ['HS256'];
    public const ACCESS_REQUEST_PARAMS =
        [
            'scope',
        ];
    public const ID_REQUEST_PARAMS =
        [

        ];
    private const ACCESS_TOKEN_PARAMS =
        [
            'scope',
            'exp',
            'iat',
        ];
    private const ID_TOKEN_PARAMS =
        [
            'uid',
            'uname',
            'email',
            'exp',
            'iat',
            'auth_time',
            'aud',
            'iss',
            'sub',
        ];

    /**
     * 验证 Token 合法性
     * @param $jwt
     * @param $type
     * @return object
     * @throws \Exception
     */
    public static function verifyToken($jwt, $type)
    {
        $data = JWT::decode($jwt, env('JWT_SECRET'), self::ALGORITHMS);
        switch ($type) {
            case 'Access':
                $params = self::ACCESS_TOKEN_PARAMS;
                break;
            case 'ID':
                $params = self::ID_TOKEN_PARAMS;
                break;
            default:
                throw new \Exception('Error type of token');
        }
        foreach ($params as $item) {
            if (empty($data->$item)) {
                throw new \Exception($type . '-Token integrity verification failed');
            }
        }
        self::verifyExp($data->exp);
        return $data;
    }

    /**
     * 验证 JWT 是否过期
     * @param $exp
     */
    private static function verifyExp($exp)
    {
        if (time() > $exp) {
            throw new Exception('Token expire', 400);
        }
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
            if (empty($data[$item])) {
                throw new \Exception('Required param missing: ' . $item);
            }
            $params[$item] = $data[$item];
        }
        try {
            return JWT::encode($params, env('JWT_SECRET'), 'HS256');
        } catch (\Exception $exception) {
            return response(['error' => 'Failed to generate Access-Token']);
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

        foreach (self::ID_TOKEN_PARAMS as $item) {
            if (empty($data[$item])) {
                throw new \Exception('Required param missing: ' . $item);
            }
        }
        try {
            return JWT::encode($data, env('JWT_SECRET'), 'HS256');
        } catch (\Exception $exception) {
            throw new \Exception('Failed to generate Access-Token');
        }
    }

}
