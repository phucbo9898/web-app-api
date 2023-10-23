<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwtauth.refresh', ['only' => ['refreshToken']]);
    }

    public function login(Request $request)
    {
        $infologin = $request->only('email', 'password');
        if (!$token = Auth::attempt($infologin)) {
            return $this->getResponse(false, 'Error account', 401);
        } else {
            return $this->getResponse(true, 'Login success', 200, [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL(),
                'user_id' => Auth::id()
            ]);
        }
    }

    public function refreshToken()
    {
        return $this->getResponse(true, 'refresh_success', 200, [
            'access_token' => \auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 24 * 7, // after 1 week expired token
        ]);
    }

    public function getInfo() {
        return Auth::user();
    }
}
