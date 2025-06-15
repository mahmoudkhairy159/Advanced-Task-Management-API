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
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
