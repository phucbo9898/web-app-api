<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDeviceToken;
use App\Repositories\UserRepository;
use App\Services\FirebasePushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $firebasePushNotificationService;
    protected $userRepository;
    public function __construct(
        UserRepository $userRepository,
        FirebasePushNotificationService $firebasePushNotificationService
    )
    {
        $this->middleware('jwtauth.refresh', ['only' => ['refreshToken']]);
        $this->userRepository = $userRepository;
        $this->firebasePushNotificationService = $firebasePushNotificationService;
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

    public function refreshToken()
    {
        return $this->getResponse(true, 'refresh_success', 200, [
            'access_token' => \auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 24 * 7, // after 1 week expired token
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return $this->getResponse(true, 'logout_success', 200);
    }

    public function register(Request $request)
    {
        $checkExistEmail = User::where('email', 'like', '%' . escape_like($request['email']) . '%')->first();
        if (!empty($checkExistEmail)) {
            return $this->getResponse(false, 'email_already_exists', 401);
        }

        try {
            DB::beginTransaction();
            $data = $request->all();
            $userInfo = $this->userRepository->prepareRegister($data);
            $this->userRepository->create($userInfo);
            DB::commit();
            return $this->getResponse(true, 'register_success', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return $this->getResponse(false, 'register_error', 401);
        }
    }
}
