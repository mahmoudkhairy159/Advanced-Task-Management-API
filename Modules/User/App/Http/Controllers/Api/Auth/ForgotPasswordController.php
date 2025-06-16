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