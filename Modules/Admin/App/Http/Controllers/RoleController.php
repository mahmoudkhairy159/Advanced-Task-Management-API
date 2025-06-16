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
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="admin"),
 *     @OA\Property(property="display_name", type="string", example="Administrator"),
 *     @OA\Property(property="description", type="string", nullable=true, example="System administrator role"),
 *     @OA\Property(property="guard_name", type="string", example="admin-api"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RoleResource",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/Role")
 * )
 *
 * @OA\Schema(
 *     schema="RoleCollection",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Role"))
 * )
 *
 * @OA\Schema(
 *     schema="StoreRoleRequest",
 *     type="object",
 *     required={"name", "display_name"},
 *     @OA\Property(property="name", type="string", example="editor"),
 *     @OA\Property(property="display_name", type="string", example="Editor"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Content editor role"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
 * )
 *
 * @OA\Schema(
 *     schema="UpdateRoleRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="updated-editor"),
 *     @OA\Property(property="display_name", type="string", example="Updated Editor"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Updated content editor role"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3, 4})
 * )
 */
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
        $this->middleware(['permission:roles.show'])->only(['index', 'show']);
        $this->middleware(['permission:roles.create'])->only(['store']);
        $this->middleware(['permission:roles.update'])->only(['update']);
        $this->middleware(['permission:roles.destroy'])->only(['destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/v1/roles",
     *     summary="Get all roles",
     *     description="Retrieve a list of all roles with optional filtering. Requires 'roles.show' permission.",
     *     operationId="getAllRoles",
     *     tags={"Role Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by role name",
     *         required=false,
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Parameter(
     *         name="display_name",
     *         in="query",
     *         description="Filter by role display name",
     *         required=false,
     *         @OA\Schema(type="string", example="Administrator")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoleCollection")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
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
     * @OA\Post(
     *     path="/api/admin/v1/roles",
     *     summary="Create new role",
     *     description="Create a new role with permissions. Requires 'roles.create' permission.",
     *     operationId="createRole",
     *     tags={"Role Management"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role creation data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Role creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role creation failed"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $data = $request->validated();
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
     * @OA\Get(
     *     path="/api/admin/v1/roles/{id}",
     *     summary="Get role by ID",
     *     description="Retrieve a specific role's information by their ID. Requires 'roles.show' permission.",
     *     operationId="getRoleById",
     *     tags={"Role Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/admin/v1/roles/{id}",
     *     summary="Update role",
     *     description="Update a role's information and permissions. Requires 'roles.update' permission.",
     *     operationId="updateRole",
     *     tags={"Role Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role update data",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Role update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role update failed"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = $this->roleRepository->find($id);
            if (!$role) {
                return abort(404);
            }

            $data = $request->validated();
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
     * @OA\Delete(
     *     path="/api/admin/v1/roles/{id}",
     *     summary="Delete role",
     *     description="Delete a role permanently. Requires 'roles.destroy' permission.",
     *     operationId="deleteRole",
     *     tags={"Role Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Role deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role deletion failed"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
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
