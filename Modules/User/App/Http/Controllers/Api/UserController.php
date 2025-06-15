<?php

namespace Modules\User\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\App\Enums\UserTypeEnum;
use Modules\User\App\Http\Requests\Api\User\ChangePasswordRequest;
use Modules\User\App\Http\Requests\Api\User\SetFcmTokenRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateAdditionalInformationRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateGeneralPreferencesRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateNotificationSettingsRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateProfessionalInformationRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateUserProfileImageRequest;
use Modules\User\App\Http\Requests\Api\User\UpdateUserRequest;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Transformers\Api\User\UserCollection;
use Modules\User\App\Transformers\Api\User\UserResource;

class UserController extends Controller
{
    use ApiResponseTrait;


    protected $userRepository;

    protected $_config;
    protected $guard;
    protected $per_page;

    public function __construct(UserRepository $userRepository)
    {
        $this->guard = 'user-api';
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userRepository = $userRepository;
        $this->per_page = config('pagination.default');
        // permissions
        $this->middleware('auth:' . $this->guard)->except([
            'index',
            'getOneByUserId',
            'showBySlug',
            'getRecommended',
        ]);
    }

    public function index()
    {
        try {
            if (!auth()->guard($this->guard)->check()) {
                request()->merge(['page' => 1]);
            }
            $data = $this->userRepository->getAllActive()->paginate($this->per_page);
            return $this->successResponse(new UserCollection($data));
        } catch (Exception $e) {

            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function getRecommended()
    {
        try {
            $data = $this->userRepository->getRecommended()->get();
            return $this->successResponse(UserResource::collection($data));
        } catch (Exception $e) {

            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function get()
    {
        try {
            $user = $this->userRepository->getActiveOneByUserId(Auth::id());
            return $this->successResponse(
                new UserResource($user),
                "",
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

    public function getOneByUserId(int $id)
    {
        try {
            $user = $this->userRepository->getActiveOneByUserId($id);
            $data = new UserResource($user);
            return $this->successResponse($data);
        } catch (Exception $e) {

            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function showBySlug(string $slug)
    {
        try {
            $data = $this->userRepository->findActiveBySlug($slug);
            if (!$data) {
                return $this->errorResponse(
                    [],
                    __('app.data-not-found'),
                    404
                );
            }
            return $this->successResponse(new UserResource($data));
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    //nomad to citizen
    public function update(UpdateUserRequest $request)
    {
        try {
            $id = auth($this->guard)->id();
            $userData = $request->only('name', 'country_id', 'city_id','nationality_id', 'phone', 'phone_code', 'image');
            //change type from Nomad to TYPE_CITIZEN
            $userData['type'] = UserTypeEnum::TYPE_CITIZEN;
            $userProfileData = $request->only('gender', 'birth_date');
            $updated = $this->userRepository->updateOne($userData, $userProfileData, $id);
            if ($updated) {
                return $this->successResponse(
                    new UserResource($updated),
                    __("user::app.users.updated-successfully"),
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            dd($e->getMessage());

            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function setFcmToken(SetFcmTokenRequest $request)
    {
        try {
            $data = $request->validated();
            $updated = $this->userRepository->setFcmToken($data['token']);
            if ($updated) {
                return $this->messageResponse(
                    __("user::app.users.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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
    public function updateUserProfileImage(UpdateUserProfileImageRequest $request)
    {
        try {
            $id = auth($this->guard)->id();
            $updated = $this->userRepository->updateUserProfileImage($id);
            if ($updated) {
                return $this->successResponse(
                    new UserResource($updated),
                    "Data updated successfully",
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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


    public function deleteUserProfileImage()
    {
        try {
            $id = auth($this->guard)->id();
            $updated = $this->userRepository->deleteUserProfileImage($id);
            if ($updated) {
                return $this->successResponse(
                    new UserResource($updated),
                    __('user::app.users.updated-successfully'),
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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
    public function changeAccountActivity()
    {
        try {
            $id = auth($this->guard)->id();
            $changed = $this->userRepository->changeAccountActivity($id);

            if ($changed) {
                auth()->guard($this->guard)->logout();
                return $this->messageResponse(
                    __('user::app.users.updated-successfully'),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            dd($e->getMessage());
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }

   

    public function updateGeneralPreferences(UpdateGeneralPreferencesRequest $request)
    {
        try {
            $id = auth($this->guard)->id();
            $data = $request->validated();
            $updated = $this->userRepository->updateGeneralPreferences($data, $id);
            if ($updated) {
                return $this->successResponse(
                    new UserResource($updated),
                    __('user::app.users.updated-successfully'),
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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
    public function updateNotificationSettings(UpdateNotificationSettingsRequest $request)
    {
        try {
            // Get the authenticated user ID
            $id = auth($this->guard)->id();

            // Get the validated request data
            $data = $request->validated();

            // Update notification preferences in the user repository
            $updated = $this->userRepository->updateNotificationSettings($data, $id);

            if ($updated) {
                return $this->successResponse(
                    new UserResource($updated),
                    __('user::app.users.updated-successfully'),
                    200
                );
            } else {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth($this->guard)->user();
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse(
                    [],
                    __("user::app.users.current-password-incorrect"),
                    422
                );
            }
            $data = $request->validated();
            $updated = $this->userRepository->changePassword($data['new_password'], $user->id);
            if ($updated) {
                return $this->messageResponse(
                    __("user::app.users.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.updated-failed"),
                    false,
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


}