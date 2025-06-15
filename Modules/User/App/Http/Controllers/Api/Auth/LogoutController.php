<?php

namespace Modules\User\App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Repositories\UserRepository;

class LogoutController extends Controller
{
    use ApiResponseTrait;


    protected $userRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserRepository $userRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        $this->middleware('auth:' . $this->guard)->only(['refresh', 'logout']);
    }


    public function logout()
    {
        try {
            auth()->guard($this->guard)->logout();
            return $this->messageResponse(
              __('user::app.auth.logout.logout_successfully'),
                true,
                200
            );
        } catch (Exception $e) {
            // return $this->errorResponse(
            //     [],
            //     __('app.something-went-wrong'),
            //     500
            // );
            return $this->errorResponse(
                [],
               $e->getMessage(),
                500
            );
        }
    }
}
