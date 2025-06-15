<?php

namespace Modules\User\App\Http\Controllers\Api;
use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Repositories\UserFileRepository;
use Modules\User\App\Transformers\Api\UserFile\UserFileResource;
use Modules\User\App\Http\Requests\Api\UserFile\StoreUserFileRequest;
use Modules\User\App\Http\Requests\Api\UserFile\UpdateUserFileRequest;

class UserFileController extends Controller
{
    use ApiResponseTrait;


    protected $userFileRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserFileRepository $userFileRepository)
    {
        $this->guard = 'user-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userFileRepository = $userFileRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);

    }
    /**
     * Display a listing of the resource.
     */




    public function getByUserId($userId)
    {
        try {
            $data = $this->userFileRepository->getByUserId($userId)->get();
            return $this->successResponse(UserFileResource::collection($data));
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserFileRequest $request)
    {
        try {
            $data =  $request->validated();
            $data['user_id'] = auth()->guard($this->guard)->id();
            $created = $this->userFileRepository->createOne($data);

            if ($created) {
                return $this->messageResponse(
                    __("user::app.userFiles.created-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("user::app.userFiles.created-failed"),
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
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $data = $this->userFileRepository->findOrFail($id);
            return $this->successResponse(new UserFileResource($data));
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
    public function update(UpdateUserFileRequest $request, $id)
    {
        try {
            $data =  $request->validated();
            $data['user_id'] = auth()->guard($this->guard)->id();
            $updated = $this->userFileRepository->updateOne($data, $id);
            if ($updated) {
                return $this->messageResponse(
                    __("user::app.userFiles.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.userFiles.updated-failed"),
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
            $deleted = $this->userFileRepository->deleteOne($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("user::app.userFiles.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("user::app.userFiles.deleted-failed"),
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
