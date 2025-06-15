<?php

namespace Modules\User\App\Http\Controllers\Admin;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Http\Requests\Admin\User\BanUserRequest;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Models\User;
use Modules\User\App\Transformers\Admin\User\UserCollection;

class UserBanController extends Controller
{
    use ApiResponseTrait;


    protected $userRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserRepository $userRepository)
    {


        $this->guard = 'admin-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:users.ban'])->only(['ban', 'unban']);
    }



    /**
     * Get only banned users.
     */
    public function getBannedUsers()
    {
        try {
            // Get all users who are banned
            $data = $this->userRepository->getOnlyBanned()->paginate();
            return $this->successResponse(new UserCollection($data));
        } catch (Exception $e) {
            return $this->errorResponse([$e->getMessage()], __('app.something-went-wrong'), 500);
        }
    }

    /**
     * Get only not banned users.
     */
    public function getNotBannedUsers()
    {
        try {
            // Get all users who are not banned
            $data = $this->userRepository->getWithoutBans()->paginate();
            return $this->successResponse(new UserCollection($data));
        } catch (Exception $e) {
            return $this->errorResponse([$e->getMessage()], __('app.something-went-wrong'), 500);
        }
    }


    public function ban(BanUserRequest $request, User $user)
    {
        try {
            if ($user->isBanned()) {
                return $this->messageResponse(
                    __("user::app.users.already-banned"),
                    false,
                    400
                );
            }

            // Ban user with an optional reason and expiration date
            $data =  $request->validated();
            $userBanned = $this->userRepository->ban($user, $data);

            if ($userBanned) {
                return $this->messageResponse(
                    __("user::app.users.banned-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.banned-failed"),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function unban(User $user)
    {
        try {

            if ($user->isNotBanned()) {
                return $this->messageResponse(
                    __("user::app.users.already-unbanned"),
                    false,
                    400
                );
            }
            $userUnbanned = $this->userRepository->unban($user);

            if ($userUnbanned) {
                return $this->messageResponse(
                    __("user::app.users.unbanned-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.unbanned-failed"),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
