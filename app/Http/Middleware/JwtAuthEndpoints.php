<?php

namespace App\Http\Middleware;

use App\Utils\Exceptions\BadEndpointException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use JWTAuth;

/**
 * Middleware para validación del JWT en el endpoint de ODTS
 */
class JwtAuthEndpoints
{
    public function handle(Request $request, Closure $next): Response
    {
        JWTAuth::getJWTProvider()->setSecret(env("ENDPOINTS_JWT_SECRET")); // cambiar secreto
        if (!JWTAuth::parseToken()->check(true)) {
            // revisar si es inválido por vencimiento
            JWTAuth::parseToken()->getClaim("exp");
            // token invalido pero no por expirado: tirar excep
            throw new BadEndpointtException();
        }

        return $next($request);
    }
}
