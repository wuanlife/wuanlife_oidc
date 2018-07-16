<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JwtVerifier;

class CheckIdToken
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
            // 检测 ID-Token 是否存在
            $id_token = $request->header(AuthController::ID_TOKEN_KEY);
            if (!$id_token) {
                return response(['error' => 'Lack ID-Token'], 401);
            }
            // 检测 ID-Token 合法性
            $data = JwtVerifier::verifyToken($id_token, 'ID');
            $request->attributes->add(['id-token' => $data]);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], 400);
        }

        return $next($request);
    }
}
