<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Repositories\UserRepository;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="LogoutResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Logged out successfully"),
 *     @OA\Property(property="data", type="object", nullable=true, example=null)
 * )
 */
class LogoutController extends Controller
{
    use ApiResponseTrait;


    protected $userRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserRepository $userRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        $this->middleware('auth:' . $this->guard)->only(['refresh', 'logout']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="User logout",
     *     description="Logout the currently authenticated user and invalidate their JWT token",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(ref="#/components/schemas/LogoutResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function logout()
    {
        try {
            auth()->guard($this->guard)->logout();
            return $this->messageResponse(
                __('user::app.auth.logout.logout_successfully'),
                true,
                200
            );
        } catch (Exception $e) {
            // return $this->errorResponse(
            //     [],
            //     __('app.something-went-wrong'),
            //     500
            // );
            return $this->errorResponse(
                [],
                $e->getMessage(),
                500
            );
        }
    }
}
