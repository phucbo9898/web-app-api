<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Laravel API",
 *     description="API Documentation for Laravel Application",
 *     @OA\Contact(
 *         email="phucbo9898@gmail.com"
 *     ),
 *      @OA\Server(
 *       url=L5_SWAGGER_CONST_HOST,
 *       description="Smile Line Api Server"
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     ),
 *     @OA\SecurityScheme(
 *      securityScheme="jwt_token",
 *      type="http",
 *      scheme="bearer"
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // Custom response
    public function getResponse($status, $message, $code = 200, $data = null)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'item' => $data
        ], $code);
    }
}
