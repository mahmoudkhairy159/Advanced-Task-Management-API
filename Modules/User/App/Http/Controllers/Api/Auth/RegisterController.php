<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\User\App\Http\Requests\Api\Auth\UserRegisterRequest;
use Modules\User\App\Repositories\OtpRepository;
use Modules\User\App\Repositories\UserProfileRepository;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Transformers\Api\User\UserResource;
use Modules\User\App\Traits\UserOtpTrait;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"first_name", "last_name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="first_name", type="string", description="User's first name", example="John"),
 *     @OA\Property(property="last_name", type="string", description="User's last name", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", format="password", description="User password (min 8 characters)", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", description="Password confirmation", example="password123"),
 *     @OA\Property(property="phone", type="string", description="User phone number", example="+1234567890")
 * )
 *
 * @OA\Schema(
 *     schema="RegisterResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Registration successful. Verification code sent to your email."),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="user", type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *             @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *             @OA\Property(property="verified_at", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         ),
 *         @OA\Property(property="token", type="string", description="JWT access token", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *         @OA\Property(property="expires_in_minutes", type="integer", description="Token expiration time in minutes", example=60)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserProfileResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="User profile retrieved successfully"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *         @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *         @OA\Property(property="verified_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="status", type="boolean", description="Account status", example=true),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     )
 * )
 */
class RegisterController extends Controller
{

    use ApiResponseTrait, UserOtpTrait;

    protected $userRepository;
    protected $otpRepository;
    protected $userProfileRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserRepository $userRepository, UserProfileRepository $userProfileRepository, OtpRepository $otpRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        $this->userProfileRepository = $userProfileRepository;
        $this->otpRepository = $otpRepository;
        $this->middleware('auth:' . $this->guard)->only(['me']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="User registration",
     *     description="Register a new user account. Automatically sends email verification code after successful registration.",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Registration data",
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(ref="#/components/schemas/RegisterResponse")
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
    protected function create(UserRegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $this->userRepository->createQuietly($data);
            $user = $this->userRepository->getActiveOneByUserId($user->id);
            DB::commit();

            // Generate JWT token for the user
            $jwtToken = JWTAuth::fromUser($user);

            $this->sendOtpCode($user);

            $user = new UserResource($user);
            $data = [
                'user' => new UserResource($user),
                'token' => $jwtToken,
                'expires_in_minutes' => Auth::factory()->getTTL()
            ];


            return $this->successResponse(
                $data,
                __('user::app.auth.register.success_register_message'),
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Get current user profile",
     *     description="Retrieve the profile information of the currently authenticated user",
     *     operationId="getCurrentUser",
     *     tags={"Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or expired token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function me()
    {
        try {
            $id = auth($this->guard)->id();
            $user = $this->userRepository->getActiveOneByUserId($id);
            return $this->successResponse(
                new UserResource($user),
                __('user::app.auth.login.logged_in_successfully'),
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
