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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Auth"},
     *     summary="Login account",
     *     description="Token by account login",
     *     security={ {"bearer": {} }},
     *     @OA\Parameter(
     *        name="email",
     *        in="query",
     *        required=true,
     *        description="email",
     *        @OA\Schema(
     *            type="string",
     *            example="admin@admin.com"
     *        )
     *    ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="password",
     *         @OA\Schema(
     *             type="string",
     *             example="secret123"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
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

    /**
     * @OA\Post (
     *     path="/api/v1/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout account",
     *     description="Logout account",
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function logout()
    {
        Auth::logout();
        return $this->getResponse(true, 'logout_success', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Auth"},
     *     summary="Register account",
     *     description="Register account",
     *     security={ {"bearer": {} }},
     *     @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                  @OA\Property(
     *                       property="image",
     *                       type="string",
     *                       format="binary",
     *                   ),
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      enum={
     *                          "admin",
     *                          "system_admin",
     *                          "user"
     *                      },
     *                      description="role account",
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      example="example@example.com",
     *                      description="Email account"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      example="secret123",
     *                      description="Password account"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Admin Acount",
     *                      description="Name account"
     *                  ),
     *                  @OA\Property(
     *                       property="phone",
     *                       type="number",
     *                       example="0123456789",
     *                       description="Phone number account"
     *                   ),
     *                   @OA\Property(
     *                        property="address",
     *                        type="string",
     *                        example="Thai Binh",
     *                        description="Address account"
     *                   ),
     *                   required={"image", "name", "email", "password", "type", "phone", "address"}
     *              )
     *           )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
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
