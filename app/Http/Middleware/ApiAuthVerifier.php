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
            if (!$request->input('app') or
                !$secret = env(strtoupper($request->input('app')) . '_SECRET')
            ) {
                throw new \Exception('illegal request');
            } elseif (!$info = $request->input('info')) {
                throw new \Exception('The info field is required.');
            } elseif (!$key = $request->input('key')) {
                throw new \Exception('The key field is required.');
            };

            // 应用名、请求时间、过期时间
            $require = ['app', 'iat', 'exp',];
            $info_d = json_decode($info);
            foreach ($require as $item) {
                if (empty($info_d->$item)) {
                    throw new \Exception('The ' . $item . ' item is required');
                }
            }
            if ($info_d->exp < time()) {
                throw new \Exception('Request is expired');
            }

            if (!Hash::check($info . $secret, $key)) {
                throw new \Exception('Fail to verify auth');
            }

        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
        return $next($request);
    }
}
