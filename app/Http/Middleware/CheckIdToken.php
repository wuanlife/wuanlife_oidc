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
                return \response(['error' => '缺少ID-Token'], 400);
            }
            // 检测 ID-Token 合法性
            JwtVerifier::verifyToken($id_token, 'ID');
        } catch (\Exception $JWTException) {
            return \response(['error' => $JWTException->getMessage()], 400);
        }

        return $next($request);
    }
}
