<?php

namespace Modules\User\App\Http\Controllers\Admin;
use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Types\CacheKeysType;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Repositories\UserFileRepository;
use Modules\User\App\Transformers\Admin\UserFile\UserFileResource;

class UserFileController extends Controller
{
    use ApiResponseTrait;


    protected $userFileRepository;

    protected $_config;
    protected $guard;

    public function __construct(UserFileRepository $userFileRepository)
    {
        $this->guard = 'admin-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userFileRepository = $userFileRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:users.show'])->only([ 'getByUserId','show']);

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



}
