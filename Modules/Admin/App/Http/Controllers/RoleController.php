<?php

namespace Modules\Admin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\App\Http\Requests\Role\StoreRoleRequest;
use Modules\Admin\App\Http\Requests\Role\UpdateRoleRequest;
use Modules\Admin\App\Repositories\RoleRepository;
use Modules\Admin\App\Transformers\Role\RoleResource;

class RoleController extends Controller
{
    use ApiResponseTrait;

    protected $roleRepository;

    protected $_config;
    protected $guard;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->guard = 'admin-api';

        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->roleRepository = $roleRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:roles.show'])->only(['index','show']);
        $this->middleware(['permission:roles.create'])->only(['store']);
        $this->middleware(['permission:roles.update'])->only(['update']);
        $this->middleware(['permission:roles.destroy'])->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->roleRepository->filter(request()->all())->get();
            return $this->successResponse(RoleResource::collection($data));
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
    public function store(StoreRoleRequest $request)
    {
        try {
            $data =  $request->validated();
            $data['created_by'] = auth()->guard($this->guard)->id();
            $created = $this->roleRepository->create($data);

            if ($created) {
                return $this->messageResponse(
                    __("admin::app.roles.created-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("admin::app.roles.created-failed"),
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
            $data = $this->roleRepository->find($id);
            return $this->successResponse(new RoleResource($data));
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
    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = $this->roleRepository->find($id);
            if (!$role) {
                return abort(404);
            }

            $data =  $request->validated();
            $data['updated_by'] = auth()->guard($this->guard)->id();
            $updated = $this->roleRepository->update($data, $id);

            if ($updated) {
                return $this->messageResponse(
                    __("admin::app.roles.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("admin::app.roles.updated-failed"),
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
            $role = $this->roleRepository->find($id);
            if (!$role) {
                return abort(404);
            }
            $deleted = $this->roleRepository->delete($id);

            if ($deleted) {
                return $this->messageResponse(
                    __("admin::app.roles.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("admin::app.roles.deleted-failed"),
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
