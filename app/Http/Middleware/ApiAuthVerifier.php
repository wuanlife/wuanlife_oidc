<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;

class ApiAuthVerifier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!$request->query('app') or
                !$secret = env(strtoupper($request->input('app')) . '_SECRET')
            ) {
                throw new \Exception('未允许的请求来源');
            } elseif (!$info = $request->query('info')) {
                throw new \Exception('缺少必要参数：info');
            } elseif (!$key = $request->query('key')) {
                throw new \Exception('缺少必要参数：key');
            };

            // 应用名、请求时间、过期时间
            $require = ['app', 'iat', 'exp',];
            $info_d = json_decode($info);
            foreach ($require as $item) {
                if (empty($info_d->$item)) {
                    throw new \Exception('缺少必要信息：' . $item);
                }
            }
            if ($info_d->exp < time()) {
                throw new \Exception('请求已过期');
            }

            if (!Hash::check($info . $secret, $key)) {
                throw new \Exception('权限验证失败');
            }

        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
        return $next($request);
    }
}
