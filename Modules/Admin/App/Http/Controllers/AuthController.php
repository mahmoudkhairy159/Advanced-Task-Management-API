<?php

namespace Modules\Admin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\App\Http\Requests\Auth\AdminLoginRequest;
use Modules\Admin\App\Http\Requests\Auth\AuthUpdateAdminRequest;
use Modules\Admin\App\Repositories\AdminRepository;
use Modules\Admin\App\Transformers\Admin\AdminResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="AdminLoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", description="Admin email address", example="admin@example.com"),
 *     @OA\Property(property="password", type="string", description="Admin password", example="password123")
 * )
 *
 * @OA\Schema(
 *     schema="AdminLoginResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Logged in successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="admin", ref="#/components/schemas/Admin"),
 *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *         @OA\Property(property="expires_in_minutes", type="integer", example=60)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AdminUpdateRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="Updated Admin Name"),
 *     @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, nullable=true, example="newpassword123"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890")
 * )
 *
 * @OA\Schema(
 *     schema="AdminTokenRefreshResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example=""),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *         @OA\Property(property="expires_in_minutes", type="integer", example=60)
 *     )
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Contains current guard
     *
     * @var string
     */
    protected $guard;
    protected $adminRepository;
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;


    public function __construct(AdminRepository $adminRepository)
    {

        $this->guard = 'admin-api';



        Auth::setDefaultDriver($this->guard);


        $this->_config = request('_config');

        $this->adminRepository = $adminRepository;

        $this->middleware('auth:' . $this->guard)->except('create');

    }


    /**
     * @OA\Post(
     *     path="/api/admin/v1/auth/login",
     *     summary="Admin login",
     *     description="Authenticate an administrator and return JWT token. Account must be active and not blocked.",
     *     operationId="adminLogin",
     *     tags={"Admin Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin login credentials",
     *         @OA\JsonContent(ref="#/components/schemas/AdminLoginRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Login successful",
     *         @OA\JsonContent(ref="#/components/schemas/AdminLoginResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account blocked or inactive",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Your Account Has Been Blocked"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email or Password"),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
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
    public function create(AdminLoginRequest $request)
    {
        try {
            $request->validated();

            if (!$jwtToken = Auth::guard($this->guard)->attempt($request->only(['email', 'password']))) {
                return $this->errorResponse(
                    [],
                    "Invalid email or Password",
                    401
                );
            }

            $admin = Auth::guard($this->guard)->user();

            if (!$admin->status || $admin->blocked) {
                $message = $admin->blocked ? "Your Account Has Been Blocked" : "Your Account Is Inactive";
                auth()->guard($this->guard)->logout();
                return $this->errorResponse(
                    [],
                    $message,
                    400
                );
            } else

                $data = [
                    'admin' => new AdminResource($admin),
                    'token' => $jwtToken,
                    'expires_in_minutes' => Auth::factory()->getTTL()
                ];

            return $this->successResponse(
                $data,
                "Logged in successfully.",
                201
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
     *     path="/api/admin/v1/auth/get-info",
     *     summary="Get admin profile",
     *     description="Retrieve the authenticated administrator's profile information",
     *     operationId="getAdminProfile",
     *     tags={"Admin Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Admin profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
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
            $admin = auth($this->guard)->user();
            return $this->successResponse(
                new AdminResource($admin),
                "Logged in successfully.",
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
     * @OA\Post(
     *     path="/api/admin/v1/auth/update-info",
     *     summary="Update admin profile",
     *     description="Update the authenticated administrator's profile information",
     *     operationId="updateAdminProfile",
     *     tags={"Admin Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin profile update data",
     *         @OA\JsonContent(ref="#/components/schemas/AdminUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
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
    public function update(AuthUpdateAdminRequest $request)
    {
        try {
            $adminId = auth($this->guard)->id();
            $data = $request->validated();

            if (!isset($data['password']) || !$data['password']) {
                unset($data['password']);
            }
            $updatedAdmin = $this->adminRepository->update($data, $adminId);
            return $this->successResponse(
                new AdminResource($updatedAdmin),
                "Data updated successfully",
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
     * @OA\Post(
     *     path="/api/admin/v1/auth/logout",
     *     summary="Admin logout",
     *     description="Logout the authenticated administrator and invalidate their JWT token",
     *     operationId="adminLogout",
     *     tags={"Admin Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
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
    public function destroy()
    {
        try {
            auth()->guard($this->guard)->logout();
            return $this->messageResponse(
                "Logged out successfully.",
                true,
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
     * @OA\Post(
     *     path="/api/admin/v1/auth/refresh-token",
     *     summary="Refresh JWT token",
     *     description="Refresh the administrator's JWT token to extend the session",
     *     operationId="refreshAdminToken",
     *     tags={"Admin Authentication"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminTokenRefreshResponse")
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
    public function refresh()
    {
        try {

            $data = [
                'access_token' => Auth::guard($this->guard)->refresh(),
                'expires_in_minutes' => Auth::factory()->getTTL(),
            ];
            return $this->successResponse(
                $data,
                "",
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
