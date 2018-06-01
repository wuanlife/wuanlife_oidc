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
        try {
            $access_token = $request->header(AuthController::ACCESS_TOKEN_KEY);
            if (!$access_token) {
                return response(['error' => 'Lack Access-Token'], 401);
            }
            $data = JwtVerifier::verifyToken($access_token, 'Access');
            $request->attributes->add(['access-token' => $data]);
        } catch (\Exception $exception) {
                return response(['error' => $exception->getMessage()], 400);
        }
        return $next($request);
    }
}
