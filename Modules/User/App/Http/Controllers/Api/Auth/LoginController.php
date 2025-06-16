<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Http\Requests\Api\Auth\UserLoginRequest;
use Modules\User\App\Models\User;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Transformers\Api\User\UserResource;
use Modules\User\App\Traits\UserOtpTrait;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="user@example.com"),
 *     @OA\Property(property="password", type="string", format="password", description="User password", example="password123"),
 *     @OA\Property(property="remember_me", type="boolean", description="Remember user login", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="LoginResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Logged in successfully"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="user", type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="user@example.com"),
 *             @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         ),
 *         @OA\Property(property="token", type="string", description="JWT access token", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *         @OA\Property(property="expires_in_minutes", type="integer", description="Token expiration time in minutes", example=60)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RefreshTokenResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="access_token", type="string", description="New JWT access token", example="eyJ0eXAiOiJKV1QiLCJhbGd..."),
 *         @OA\Property(property="expires_in_minutes", type="integer", description="Token expiration time in minutes", example=60)
 *     )
 * )
 */
class LoginController extends Controller
{
    use ApiResponseTrait, UserOtpTrait;


    protected $userRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserRepository $userRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        $this->middleware('auth:' . $this->guard)->only(['refresh']);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="User login",
     *     description="Authenticate user with email and password. Returns JWT token and user information. Also handles email verification flow.",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Login successful",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account inactive or blocked",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Your account is inactive or blocked"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email or password"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function login(UserLoginRequest $request)
    {
        try {
            $request->validated();

            if (!$jwtToken = Auth::guard($this->guard)->attempt($request->only(['email', 'password']))) {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.login.invalid_email_or_password'),
                    401
                );
            }
            $user = User::where('email', $request->email)->with('profile')
                ->first();

            if (!$user->status || $user->isBanned()) {
                $message = $user->isBanned() ? __('user::app.auth.login.your_account_is_blocked') : __('user::app.auth.login.your_account_is_inactive');
                Auth::guard($this->guard)->logout();
                return $this->errorResponse(
                    [],
                    $message,
                    400
                );
            }

            $user->last_login_at = Carbon::now();
            $user->active = User::ACTIVE;
            $user->save();
            $msg = __('user::app.auth.login.logged_in_successfully');
            if (!$user->verified_at) {
                if ($this->checkOtpCodeExpirationByUserId($user->id)) {
                    $msg = __('user::app.auth.verification.logged_in_successfully_and_Verification_code_already_sent');
                } else {
                    $this->sendOtpCode($user);
                    $msg = __('user::app.auth.login.logged_in_successfully_and_Verification_code_sent');
                }
            }
            $data = [
                'user' => new UserResource($user),
                'token' => $jwtToken,
                'expires_in_minutes' => Auth::factory()->getTTL()
            ];

            return $this->successResponse(
                $data,
                $msg,
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh-token",
     *     summary="Refresh JWT token",
     *     description="Refresh the JWT token to extend session. Requires valid JWT token in Authorization header.",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RefreshTokenResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token is invalid or expired",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function refresh()
    {
        try {

            $data = [
                'access_token' => Auth::refresh(),
                'expires_in_minutes' => Auth::factory()->getTTL(),
            ];
            return $this->successResponse(
                $data,
                "",
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
