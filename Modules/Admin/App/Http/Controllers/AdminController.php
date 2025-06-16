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
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Admin",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Admin User"),
 *     @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="AdminResource",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/Admin")
 * )
 *
 * @OA\Schema(
 *     schema="AdminCollection",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Data retrieved successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Admin")),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=50),
 *         @OA\Property(property="last_page", type="integer", example=4)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StoreAdminRequest",
 *     type="object",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", example="Admin User"),
 *     @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, example="password123"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *     @OA\Property(property="status", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateAdminRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="Updated Admin User"),
 *     @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, nullable=true, example="newpassword123"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *     @OA\Property(property="status", type="boolean", example=true)
 * )
 */
class AdminController extends Controller
{
    use ApiResponseTrait;


    protected $adminRepository;

    protected $_config;
    protected $guard;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->guard = 'admin-api';

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
     * @OA\Get(
     *     path="/api/admin/v1/admins",
     *     summary="Get all admins",
     *     description="Retrieve a paginated list of all administrators. Requires 'admins.show' permission.",
     *     operationId="getAllAdmins",
     *     tags={"Admin Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admins retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminCollection")
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
     * @OA\Post(
     *     path="/api/admin/v1/admins",
     *     summary="Create new admin",
     *     description="Create a new administrator account. Requires 'admins.create' permission.",
     *     operationId="createAdmin",
     *     tags={"Admin Management"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin creation data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreAdminRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin created successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Admin creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Admin creation failed"),
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
    public function store(StoreAdminRequest $request)
    {
        try {
            $data = $request->validated();
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
     * @OA\Get(
     *     path="/api/admin/v1/admins/{id}",
     *     summary="Get admin by ID",
     *     description="Retrieve a specific administrator's information by their ID.",
     *     operationId="getAdminById",
     *     tags={"Admin Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found",
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
     * @OA\Put(
     *     path="/api/admin/v1/admins/{id}",
     *     summary="Update admin",
     *     description="Update an administrator's information. Requires 'admins.update' permission.",
     *     operationId="updateAdmin",
     *     tags={"Admin Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin update data",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAdminRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin updated successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Admin update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Admin update failed"),
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
    public function update(UpdateAdminRequest $request, $id)
    {
        try {
            $data = $request->validated();
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
     * @OA\Delete(
     *     path="/api/admin/v1/admins/{id}",
     *     summary="Delete admin",
     *     description="Delete an administrator account. Requires 'admins.destroy' permission.",
     *     operationId="deleteAdmin",
     *     tags={"Admin Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin deleted successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Admin deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Admin deletion failed"),
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
     *         description="Admin not found",
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
