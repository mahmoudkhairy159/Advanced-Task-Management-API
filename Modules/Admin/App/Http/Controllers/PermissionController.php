<?php

namespace Modules\Admin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     @OA\Property(property="key", type="string", example="users.show"),
 *     @OA\Property(property="name", type="string", example="View Users"),
 *     @OA\Property(property="route", type="string", example="admin.users.index"),
 *     @OA\Property(property="sort", type="integer", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="PermissionGroup",
 *     type="object",
 *     @OA\Property(property="key", type="string", example="users"),
 *     @OA\Property(property="name", type="string", example="Users"),
 *     @OA\Property(property="route", type="string", example="admin.users.index"),
 *     @OA\Property(property="sort", type="integer", example=1),
 *     @OA\Property(property="children", type="array", @OA\Items(ref="#/components/schemas/Permission"))
 * )
 *
 * @OA\Schema(
 *     schema="PermissionsResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example=""),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PermissionGroup"))
 * )
 */
class PermissionController extends Controller
{
    use ApiResponseTrait;
    protected $_config;
    protected $guard;

    public function __construct()
    {
        $this->guard = 'admin-api';
        Auth::setDefaultDriver($this->guard);
        $this->middleware(['auth:' . $this->guard]);
        $this->_config = request('_config');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/v1/permissions",
     *     summary="Get all permissions",
     *     description="Retrieve a hierarchical list of all available permissions in the system organized by modules",
     *     operationId="getAllPermissions",
     *     tags={"Permission Management"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionsResponse")
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
    public function index()
    {
        try {
            return $this->successResponse(core()->getACL(), '', 200);
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
