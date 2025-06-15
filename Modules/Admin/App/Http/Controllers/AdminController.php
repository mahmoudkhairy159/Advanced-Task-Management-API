<?php

namespace Modules\Admin\App\Http\Controllers;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\App\Repositories\AdminRepository;
use Modules\Admin\App\Transformers\Admin\AdminResource;
use Modules\Admin\App\Http\Requests\Admin\StoreAdminRequest;
use Modules\Admin\App\Http\Requests\Admin\UpdateAdminRequest;
use Modules\Admin\App\Transformers\Admin\AdminCollection;

class AdminController extends Controller
{
    use ApiResponseTrait;


    protected $adminRepository;

    protected $_config;
    protected $guard;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->guard = 'admin-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->adminRepository = $adminRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:admins.show'])->only(['index']);
        $this->middleware(['permission:admins.create'])->only(['store']);
        $this->middleware(['permission:admins.update'])->only(['update']);
        $this->middleware(['permission:admins.destroy'])->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->adminRepository->getAll()->paginate();
            return $this->successResponse(new AdminCollection($data));
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
    public function store(StoreAdminRequest $request)
    {
        try {
            $data =  $request->validated();
            $data['created_by'] = auth()->guard($this->guard)->id();
            $created = $this->adminRepository->create($data);
            if ($created) {
                return $this->messageResponse(
                    __("admin::app.admins.created-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("admin::app.admins.created-failed"),
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
            $data = $this->adminRepository->findOrFail($id);
            return $this->successResponse(new AdminResource($data));
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
    public function update(UpdateAdminRequest $request, $id)
    {
        try {
            $data =  $request->validated();
            $data['updated_by'] = auth()->guard($this->guard)->id();
            if (!isset($data['password']) || !$data['password']) {
                unset($data['password']);
            }
            $updated = $this->adminRepository->updateOne($data, $id);
            if ($updated) {
                return $this->messageResponse(
                    __("admin::app.admins.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("admin::app.admins.updated-failed"),
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
            $deleted = $this->adminRepository->deleteOne($id);

            if ($deleted) {
                return $this->messageResponse(
                    __("admin::app.admins.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("admin::app.admins.deleted-failed"),
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
