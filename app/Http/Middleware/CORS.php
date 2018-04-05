<?php

namespace App\Http\Middleware;

use Closure;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

        public function handle($request, Closure $next)
    {

        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Access-Toke,ID-Token');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
        //$response->header('Access-Control-Allow-Credentials', 'false');
        return $response;
    }
}
