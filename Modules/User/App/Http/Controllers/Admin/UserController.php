<?php

namespace Modules\User\App\Http\Controllers\Admin;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Repositories\UserRepository;
use Modules\User\App\Transformers\Admin\User\UserResource;
use Modules\User\App\Http\Requests\Admin\User\StoreUserRequest;
use Modules\User\App\Http\Requests\Admin\User\UpdateUserRequest;
use Modules\User\App\Transformers\Admin\User\UserCollection;

class UserController extends Controller
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
        $this->middleware(['permission:users.show'])->only(['index']);
        $this->middleware(['permission:users.create'])->only(['store']);
        $this->middleware(['permission:users.update'])->only(['update']);
        $this->middleware(['permission:users.destroy'])->only(['destroy', 'deletePermanently']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->userRepository->getAll()->paginate();
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
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $userData = $request->validated();
            $userData = $request->only(
                'name',
                'email',
                'phone',
                'phone_code',
                'status',
                'blocked',
                'nationality_id',
                'country_id',
                'city_id',
                'educational_level_id',
                'professional_specialization_id',
                'password',
                'image',
                'resume',
            );
            $userProfileData = $request->only(
                'bio',
                'language',
                'mode',
                'sound_effects',
                'allow_related_notifications',
                'send_email_notifications',
                'gender',
                'birth_date',
                'university',
                'current_company_name',
                'current_job_type',
                'current_job_position',
                'political_affiliations',
            );
            $userData['created_by'] = auth()->guard($this->guard)->id();
            $userCreated = $this->userRepository->createOneByAdmin($userData, $userProfileData);
            if ($userCreated) {
                $verified = $this->userRepository->verify($userCreated->id);
                return $this->messageResponse(
                    __("user::app.users.created-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.created-failed"),
                    false,
                    400
                );
            }
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
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $user = $this->userRepository->getOneByUserId($id);
            if (!$user) {
                abort(404);
            }
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
            $data = $this->userRepository->findBySlug($slug);
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
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {

            $userData = $request->validated();
            $userData = $request->only(
                'name',
                'email',
                'phone',
                'phone_code',
                'status',
                'blocked',
                'nationality_id',
                'country_id',
                'city_id',
                'type',
                'educational_level_id',
                'professional_specialization_id',
                'password',
                'image',
                'resume',
            );
            $userProfileData = $request->only(
                'bio',
                'language',
                'mode',
                'sound_effects',
                'allow_related_notifications',
                'send_email_notifications',
                'gender',
                'birth_date',
                'university',
                'current_company_name',
                'current_job_type',
                'current_job_position',
                'political_affiliations',
            );
            $userData['updated_by'] = auth()->guard($this->guard)->id();
            if (!isset($userData['password']) || !$userData['password']) {
                unset($userData['password']);
            }
            $updated = $this->userRepository->updateOne($userData, $userProfileData, $id);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $deleted = $this->userRepository->deleteOne($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("user::app.users.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.deleted-failed"),
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

    public function deletePermanently($id)
    {
        try {

            $deleted = $this->userRepository->deletePermanently($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("user::app.users.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.users.deleted-failed"),
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
