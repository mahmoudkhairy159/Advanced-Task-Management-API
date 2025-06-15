<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  Modules\User\App\Http\Requests\Api\Auth\UserResetPasswordRequest;
use Modules\User\App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Traits\UserOtpTrait;

class ResetPasswordController extends Controller
{
    use ApiResponseTrait,UserOtpTrait;

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

            $status= $user->forceFill([
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

            $user = $this->userRepository->where('email',$request->email)->first();
            $otpCode = $request->code;
            $isValidOtp = $this->isValidOtpCode($user, $otpCode);


            if (!$isValidOtp){
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
