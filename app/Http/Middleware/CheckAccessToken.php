<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JwtVerifier;

class CheckAccessToken
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
        $access_token = $request->header(AuthController::ACCESS_TOKEN_KEY);
        if (!$access_token) {
            return \response(['error' => '缺少Access-Token'], 400);
        }

        try {
            $data = JwtVerifier::verifyToken($access_token, 'Access');
            $request->attributes->add(['access-token' => $data]);
        } catch (\Exception $JWTException) {
            return \response(['error' => $JWTException->getMessage()], 400);
        }
        return $next($request);
    }
}
