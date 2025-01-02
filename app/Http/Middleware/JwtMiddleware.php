<?php

namespace App\Http\Middleware;

use Closure;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException) {
            return response()->json(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.'], 401);
        }

        return $next($request);
    }
}
