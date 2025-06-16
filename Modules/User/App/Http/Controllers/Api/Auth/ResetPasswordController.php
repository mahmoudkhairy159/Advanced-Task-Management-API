<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Http\Requests\Api\Auth\UserResetPasswordRequest;
use Modules\User\App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Traits\UserOtpTrait;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ResetPasswordRequest",
 *     type="object",
 *     required={"email", "password", "password_confirmation", "code"},
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="user@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, description="New password", example="newpassword123"),
 *     @OA\Property(property="password_confirmation", type="string", description="Password confirmation", example="newpassword123"),
 *     @OA\Property(property="code", type="string", description="OTP verification code", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="ResetPasswordResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Password reset successfully"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="VerifyOtpRequest",
 *     type="object",
 *     required={"email", "code"},
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="user@example.com"),
 *     @OA\Property(property="code", type="string", description="OTP verification code", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="VerifyOtpResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Valid OTP code"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 */
class ResetPasswordController extends Controller
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
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password",
     *     summary="Reset password",
     *     description="Reset user password using OTP code received via email. The OTP code must be valid and not expired.",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reset password request data",
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP code or reset failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP code"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found"),
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
    public function reset(UserResetPasswordRequest $request): JsonResponse
    {
        try {


            $credentials = $request->only('email', 'password', 'password_confirmation', 'code');
            $otpCode = $request->code;
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.forgotPassword.user_not_found'),
                    404
                );
            }
            $isValidOtp = $this->isValidOtpCode($user, $otpCode);
            if (!$isValidOtp) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.invalid_otp'),
                    400
                );
            }

            $status = $user->forceFill([
                'password' => $request->password,
                'remember_token' => Str::random(60),
            ])->save();
            event(new PasswordReset($user));


            if ($status) {
                return $this->successResponse(
                    [],
                    __('user::app.auth.resetPassword.reset-successfully'),
                    200
                );
            } else {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.resetPassword.reset-failed'),
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-otp",
     *     summary="Verify OTP code",
     *     description="Verify OTP code for password reset. This endpoint validates the OTP code without resetting the password.",
     *     operationId="verifyOtpCode",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="OTP verification request data",
     *         @OA\JsonContent(ref="#/components/schemas/VerifyOtpRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Valid OTP code",
     *         @OA\JsonContent(ref="#/components/schemas/VerifyOtpResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP code",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP code"),
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
    public function verify(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => ['required', 'alpha_num'],
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }

            $user = $this->userRepository->where('email', $request->email)->first();
            $otpCode = $request->code;
            $isValidOtp = $this->isValidOtpCode($user, $otpCode);


            if (!$isValidOtp) {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.invalid_otp'),
                    400
                );
            }

            return $this->successResponse(
                [],
                __('user::app.auth.verification.valid_otp'),
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
