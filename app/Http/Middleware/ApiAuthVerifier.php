<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
                return response(['error' => 'illegal request'], 400);
            } elseif (!$info = $request->input('info')) {
                return response(['error' => 'The info field is required.'], 422);
            } elseif (!$key = $request->input('key')) {
                return response(['error' => 'The key field is required.'], 422);
            };

            $validator = Validator::make($request->all(),
                [
                    'info' => 'required',
                    'key' => 'required',
                ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            $info_d = json_decode($info);
            $validator = Validator::make($info_d,
                [
                    'app' => 'required',    // 应用名
                    'iat' => 'required',    // 请求时间
                    'exp' => 'required',    // 过期时间
                ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            if ($info_d->exp < time()) {
                return response(['error' => 'Request is expired'], 400);
            }

            if (!Hash::check($info . $secret, $key)) {
                return response(['error' => 'Failed to verify auth'], 403);
            }
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }
        return $next($request);
    }
}
