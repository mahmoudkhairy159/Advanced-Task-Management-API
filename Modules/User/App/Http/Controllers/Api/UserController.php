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
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="phone_code", type="string", example="+1"),
 *     @OA\Property(property="slug", type="string", example="john-doe"),
 *     @OA\Property(property="type", type="string", enum={"nomad", "citizen"}, example="citizen"),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://example.com/image.jpg"),
 *     @OA\Property(property="verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *     schema="UserCollection",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=100),
 *         @OA\Property(property="last_page", type="integer", example=7)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="country_id", type="integer", example=1),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="nationality_id", type="integer", example=1),
 *     @OA\Property(property="phone", type="string", example="1234567890"),
 *     @OA\Property(property="phone_code", type="string", example="+1"),
 *     @OA\Property(property="image", type="string", format="binary", nullable=true),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01")
 * )
 *
 * @OA\Schema(
 *     schema="SetFcmTokenRequest",
 *     type="object",
 *     required={"token"},
 *     @OA\Property(property="token", type="string", description="Firebase Cloud Messaging token", example="fcm_token_here")
 * )
 *
 * @OA\Schema(
 *     schema="ChangePasswordRequest",
 *     type="object",
 *     required={"current_password", "new_password", "new_password_confirmation"},
 *     @OA\Property(property="current_password", type="string", description="Current password", example="current_password"),
 *     @OA\Property(property="new_password", type="string", minLength=8, description="New password", example="new_password123"),
 *     @OA\Property(property="new_password_confirmation", type="string", description="New password confirmation", example="new_password123")
 * )
 *
 * @OA\Schema(
 *     schema="MessageResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object", nullable=true, example=null)
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/api/user/v1/users",
     *     summary="Get all users",
     *     description="Retrieve a paginated list of all active users. Authentication is optional but affects pagination.",
     *     operationId="getAllUsers",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserCollection")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/user/v1/users/recommended",
     *     summary="Get recommended users",
     *     description="Retrieve a list of recommended users based on the platform's algorithm",
     *     operationId="getRecommendedUsers",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Recommended users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/user/v1/profile",
     *     summary="Get current user profile",
     *     description="Retrieve the authenticated user's profile information",
     *     operationId="getCurrentUserProfile",
     *     tags={"Users"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/user/v1/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieve a specific user's profile information by their ID",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/user/v1/users/slug/{slug}",
     *     summary="Get user by slug",
     *     description="Retrieve a specific user's profile information by their unique slug",
     *     operationId="getUserBySlug",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="User unique slug",
     *         required=true,
     *         @OA\Schema(type="string", example="john-doe")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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
            $userData = $request->only('name', 'country_id', 'city_id', 'nationality_id', 'phone', 'phone_code', 'image');
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

    /**
     * @OA\Put(
     *     path="/api/user/v1/change-password",
     *     summary="Change password",
     *     description="Change the authenticated user's password. Requires current password verification.",
     *     operationId="changeUserPassword",
     *     tags={"Users"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Password change request data",
     *         @OA\JsonContent(ref="#/components/schemas/ChangePasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Password change failed",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or incorrect current password",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Current password is incorrect"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
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
