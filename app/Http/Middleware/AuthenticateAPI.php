<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateAPI
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربر احراز هویت نشده'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'توکن نامعتبر یا منقضی شده'
            ], 401);
        }

        return $next($request);
    }
}
