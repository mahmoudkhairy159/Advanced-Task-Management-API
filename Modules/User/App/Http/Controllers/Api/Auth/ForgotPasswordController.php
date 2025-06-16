<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Http\Requests\Api\ForgotPassword\ForgotPasswordRequest;
use Modules\User\App\Http\Requests\Api\ForgotPassword\ForgotPasswordResentCodeRequest;
use Modules\User\App\Traits\UserOtpTrait;
use Modules\User\App\Repositories\UserRepository;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ForgotPasswordRequest",
 *     type="object",
 *     required={"email"},
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="user@example.com")
 * )
 *
 * @OA\Schema(
 *     schema="ForgotPasswordResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="OTP code sent to your email successfully"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="ResendCodeRequest",
 *     type="object",
 *     required={"email"},
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="user@example.com")
 * )
 *
 * @OA\Schema(
 *     schema="ResendCodeResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="OTP code resent successfully"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 */
class ForgotPasswordController extends Controller
{
    use ApiResponseTrait, UserOtpTrait;

    /**
     * Handle forgot password request.
     *
     * @param  Request  $request
     * @return JsonResponse
     */


    protected $_config;
    protected $guard;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     summary="Forgot password",
     *     description="Send OTP code to user email for password reset. User will receive a verification code via email.",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Forgot password request data",
     *         @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP code sent successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ForgotPasswordResponse")
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
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {

        try {
            $credentials = $request->validated();
            $user = $this->userRepository->where('email', $credentials['email'])->first();

            if (!$user) {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.forgotPassword.user_not_found'),
                    404
                );
            }

            $this->sendResetPasswordOtpCode($user);
            return $this->successResponse(
                [],
                __('user::app.auth.forgotPassword.otp_code_email_sent_successfully'),
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password/resend-otp-code",
     *     summary="Resend OTP code",
     *     description="Resend OTP code for password reset if the previous one expired or was not received",
     *     operationId="resendOtpCode",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Resend code request data",
     *         @OA\JsonContent(ref="#/components/schemas/ResendCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP code resent successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ResendCodeResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot resend OTP code at this time",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot resend verification OTP code"),
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
    public function resendCode(ForgotPasswordResentCodeRequest $request)
    {
        try {

            $credentials = $request->validated();
            $user = $this->userRepository->where('email', $credentials['email'])->first();

            if (!$user) {
                return $this->errorResponse(
                    [],
                    __('user::app.auth.forgotPassword.user_not_found'),
                    404
                );
            }


            $isrResented = $this->resendOtpCode($user);

            if (!$isrResented) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.cant_resend_verification_otp_code'),
                    400
                );
            }
            return $this->successResponse(
                [],
                __('user::app.auth.verification.verification_otp_code_resend_successfully'),
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
