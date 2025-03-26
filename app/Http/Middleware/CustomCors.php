<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = [
            "https://visionremota.com.mx:3000", // para producción, podría no funcionar CORS
            "http://visionremota.com.mx:3000", // para producción, podría no funcionar CORS
            "https://visionremota.com.mx", // para producción, podría no funcionar CORS
            "http://visionremota.com.mx", // para producción, podría no funcionar CORS
            "http://localhost:3000",
            "https://localhost:3000",
        ];

        $origin = array_key_exists("HTTP_ORIGIN", $_SERVER)
            ? $_SERVER["HTTP_ORIGIN"]
            : array_key_exists("HTTP_REFERER", $_SERVER) ?? null;
        if ($origin) {
            if (in_array($origin, $allowedOrigins)) {
                return $next($request)
                    ->header("Access-Control-Allow-Origin", $origin)
                    ->header(
                        "Access-Control-Allow-Methods",
                        "GET, HEAD, OPTIONS, POST, PUT, DELETE"
                    )
                    ->header("Access-Control-Allow-Headers", "Content-Type");
            }
        }

        return $next($request); // ignore, may or may not work
        /*return $next($request)->header(
            "Access-Control-Allow-Origin",
            "http://localhost:3000,https://visionremota.com.mx/"
        );*/
    }
}
