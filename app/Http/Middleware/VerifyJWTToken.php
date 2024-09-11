<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'status_code' => 'INVALID_USER',
                ], 401);
            } else {
                if ($user->active != 1) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 'INVALID_STATUS_USER',
                    ], 401);
                }
            }
        } catch (TokenExpiredException $exception) {
            Log::debug('TokenExpiredException: ' . $exception->getMessage());
            try {
//                Auth::logout();
                return response()->json([
                    'status' => false,
                    'status_code' => 'TOKEN_EXPIRED',
                ], 401);
            } catch (JWTException $e) {
                Log::debug('JWTException: ' . $e->getMessage());
//                Auth::logout();
                return response()->json([
                    'status' => false,
                    'status_code' => 'INVALID_TOKEN',
                ], 401);
            }
        }
        catch (JWTException $e) {
            Log::debug('JWTException: ' . $e->getMessage());
//            Auth::logout();
            return response()->json([
                'status' => false,
                'status_code' => 'INVALID_TOKEN',
            ], 401);
        }
        return $next($request);
    }
}
