<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\User\App\Traits\UserOtpTrait;
use Modules\User\App\Repositories\UserRepository;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="VerificationCodeRequest",
 *     type="object",
 *     required={"code"},
 *     @OA\Property(property="code", type="string", description="Email verification code", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="VerificationResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Email verified successfully"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="ResendVerificationResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Verification code resent successfully"),
 *     @OA\Property(property="data", type="object", example={})
 * )
 */
class VerificationController extends Controller
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
        $this->middleware('auth:' . $this->guard);
        // $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/email/resend",
     *     summary="Resend email verification code",
     *     description="Resend email verification OTP code to the authenticated user. Rate limited to 6 attempts per minute.",
     *     operationId="resendVerificationCode",
     *     tags={"Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Verification code resent successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ResendVerificationResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Already verified, code already sent, or cannot resend",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Email already verified"),
     *                     @OA\Property(property="errors", type="object", example={})
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Verification code already sent"),
     *                     @OA\Property(property="errors", type="object", example={})
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests - Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/RateLimitError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function resend(Request $request)
    {
        try {


            $user = Auth::user();

            if ($user->verified_at) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.already_verified'),
                    400
                );
            }
            if ($this->checkOtpCodeExpirationByUserId($user->id)) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.already_sent_verification_otp_code'),
                    400
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
            // return  $this->messageResponse( $e->getMessage());
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/email/verify",
     *     summary="Verify email address",
     *     description="Verify the authenticated user's email address using OTP code. Rate limited to 6 attempts per minute.",
     *     operationId="verifyEmail",
     *     tags={"Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Email verification request data",
     *         @OA\JsonContent(ref="#/components/schemas/VerificationCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Email verified successfully",
     *         @OA\JsonContent(ref="#/components/schemas/VerificationResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Already verified, invalid OTP, or verification failed",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Email already verified"),
     *                     @OA\Property(property="errors", type="object", example={})
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Invalid OTP code"),
     *                     @OA\Property(property="errors", type="object", example={})
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests - Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/RateLimitError")
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
                'code' => ['required', 'alpha_num']
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    $validator->errors(),
                    'Validation Error',
                    422
                );
            }
            $user = Auth::user();

            if ($user->verified_at) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.already_verified'),
                    400
                );
            }

            $otpCode = $request->code;
            $isValidOtp = $this->isValidOtpCode($user, $otpCode);


            if (!$isValidOtp) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.invalid_otp'),
                    400
                );
            }



            $verified = $this->userRepository->verify($user->id);
            if (!$verified) {

                return $this->errorResponse(
                    [],
                    __('user::app.auth.verification.verification_failed'),
                    400
                );
            }

            return $this->successResponse(
                [],
                __('user::app.auth.verification.verified_successfully'),
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
