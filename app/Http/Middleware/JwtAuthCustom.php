<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use JWTAuth;

class JwtAuthCustom {

    public function handle(Request $request, Closure $next): Response {

        JWTAuth::parseToken()->authenticate();

        return $next($request);
    }

}
