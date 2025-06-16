<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use  Modules\User\App\Http\Requests\Api\Auth\UserLoginRequest;
use Modules\User\App\Models\User;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Transformers\Api\User\UserResource;
use Modules\User\App\Traits\UserOtpTrait;

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
     * Handle user login.
     *
     * @param UserLoginRequest $request
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
                    $msg =  __('user::app.auth.verification.logged_in_successfully_and_Verification_code_already_sent');
                } else {
                    $this->sendOtpCode($user);
                    $msg = __('user::app.auth.login.logged_in_successfully_and_Verification_code_sent');
                }
            }
            $data = [
                'user' => new UserResource($user),
                'token'   => $jwtToken,
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
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
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
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}