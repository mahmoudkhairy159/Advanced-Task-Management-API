<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use  Modules\User\App\Http\Requests\Api\Auth\UserRegisterRequest;
use Modules\User\App\Repositories\OtpRepository;
use Modules\User\App\Repositories\UserProfileRepository;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\resources\Api\User\UserResource;
use Modules\User\App\Traits\UserOtpTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     */
    protected function create(UserRegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $this->userRepository->createQuietly($data);
            $user=$this->userRepository->getActiveOneByUserId($user->id);
            DB::commit();

            // Generate JWT token for the user
            $jwtToken = JWTAuth::fromUser($user);

            $this->sendOtpCode($user);

            $user = new UserResource($user);
            $data = [
                'user' => new UserResource($user),
                'token'   => $jwtToken,
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
     */
    public function me()
    {
        try {
            $id = auth($this->guard)->id();
            $user=$this->userRepository->getActiveOneByUserId($id);
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
