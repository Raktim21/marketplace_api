<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authHeader = $request->header('Authorization');

            if (!$authHeader) {
                return response()->json([
                    'status' => false,
                    'error' => 'Unauthorized: No Authorization header provided'
                ], 401);
            }

            // ✅ Remove 'Bearer ' if present
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            } else {
                $token = $authHeader;
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'error' => 'User not found'
                ], 404);
            }

        } catch (JWTException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}

