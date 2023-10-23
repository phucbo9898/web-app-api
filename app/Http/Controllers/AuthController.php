<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $infologin = $request->only('email', 'password');
        if (!$token = Auth::attempt($infologin)) {
            return $this->getResponse(false, 'Error account', 401);
        } else {
            return $this->getResponse(true, 'Login success', 200, [
                'token' => $token,
                'user_id' => Auth::id()
            ]);
        }
    }

    public function getInfo() {
//        dd(1123123);
        return Auth::user();
    }
}
