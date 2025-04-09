<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Obligaa las request a esperar una respuesta JSON
 */
class ForceApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set("Accept", "application/json");

        return $next($request);
    }
}
