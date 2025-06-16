<?php

namespace Modules\Task\App\Http\Controllers\Admin;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Task\App\Http\Requests\Admin\Task\StoreTaskRequest;
use Modules\Task\App\Http\Requests\Admin\Task\UpdateTaskRequest;
use Modules\Task\App\Http\Requests\Api\Task\UpdateTaskStatusRequest;
use Modules\Task\App\Repositories\TaskRepository;
use Modules\Task\App\Transformers\Admin\Task\TaskCollection;
use Modules\Task\App\Transformers\Admin\Task\TaskResource;

/**
 * @OA\Tag(
 *     name="Admin Tasks",
 *     description="Admin Task management endpoints"
 * )
 */
class TaskController extends Controller
{
    use ApiResponseTrait;
    protected $taskRepository;
    protected $_config;
    protected $guard;
    public function __construct(TaskRepository $taskRepository)
    {
        $this->guard = 'admin-api';

        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->taskRepository = $taskRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:tasks.show'])->only(['index', 'show']);
        $this->middleware(['permission:tasks.create'])->only(['store']);
        $this->middleware(['permission:tasks.update'])->only(['update']);
        $this->middleware(['permission:tasks.destroy'])->only(['destroy', 'forceDelete', 'restore', 'getOnlyTrashed']);
    }

    /**
     * @OA\Get(
     *     path="/admin/tasks",
     *     summary="Get all tasks",
     *     description="Retrieve a paginated list of all tasks",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Task")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="total", type="integer", example=75)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     *
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->taskRepository->getAll()->paginate();
            return $this->successResponse(new TaskCollection($data));
        } catch (Exception $e) {
            dd($e->getMessage());
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/tasks",
     *     summary="Create a new task",
     *     description="Store a newly created task in storage",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task data",
     *         @OA\JsonContent(
     *             required={"title", "description", "status", "priority", "assignable_id", "assignable_type"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Complete project documentation"),
     *             @OA\Property(property="description", type="string", example="Write comprehensive documentation for the project"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="pending"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="high"),
     *             @OA\Property(property="assignable_id", type="integer", example=1),
     *             @OA\Property(property="assignable_type", type="string", example="App\\Models\\User"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-12-31"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"documentation", "urgent"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task creation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="The title field is required.")),
     *                 @OA\Property(property="status", type="array", @OA\Items(type="string", example="The selected status is invalid."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     *
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        try {
            $data = $request->validated();
            $created = $this->taskRepository->createOne($data);

            if ($created) {
                return $this->messageResponse(
                    __("task::app.tasks.created-successfully"),
                    true,
                    201
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.created-failed"),
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
     *     path="/admin/tasks/{id}",
     *     summary="Get a specific task",
     *     description="Show the specified task resource",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     *
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $data = $this->taskRepository->getOneById($id);
            if (!$data) {
                return $this->errorResponse(
                    [],
                    __('app.data-not-found'),
                    404
                );
            }
            return $this->successResponse(new TaskResource($data));
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
     *     path="/admin/tasks/{id}",
     *     summary="Update a task",
     *     description="Update the specified task in storage",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255, example="Updated task title"),
     *             @OA\Property(property="description", type="string", example="Updated task description"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="in_progress"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium"),
     *             @OA\Property(property="assignable_id", type="integer", example=2),
     *             @OA\Property(property="assignable_type", type="string", example="App\\Models\\User"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-12-31"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"updated", "important"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task update failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $updated = $this->taskRepository->updateOne($data, $id);

            if ($updated) {
                return $this->messageResponse(
                    __("task::app.tasks.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.updated-failed"),
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
     * @OA\Patch(
     *     path="/admin/tasks/{id}/status",
     *     summary="Update task status",
     *     description="Update the status of a specific task",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task status data",
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task status update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task update failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Update the specified resource in storage.
     */
    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        try {
            $task = $this->taskRepository->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('assignable_id', Auth::id())
                        ->where('assignable_type', get_class(Auth::user()));
                })->orWhere(function ($q) {
                    $q->where('creator_id', Auth::id())
                        ->where('creator_type', get_class(Auth::user()));
                });
            })->findOrFail($id);
            if (!$task) {
                return $this->errorResponse(
                    [],
                    __('app.data-not-found'),
                    404
                );
            }
            $data = $request->validated();
            $updated = $this->taskRepository->updateStatus($data, $task);

            if ($updated) {
                return $this->messageResponse(
                    __("task::app.tasks.updated-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.updated-failed"),
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
     *     path="/admin/tasks/{id}",
     *     summary="Delete a task",
     *     description="Remove the specified task from storage (soft delete)",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task deletion failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     *
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->taskRepository->delete($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("task::app.tasks.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.deleted-failed"),
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


    /***********Trashed model SoftDeletes**************/

    /**
     * @OA\Get(
     *     path="/admin/tasks/trashed",
     *     summary="Get trashed tasks",
     *     description="Retrieve a paginated list of soft-deleted tasks",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trashed tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Task")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=3),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="total", type="integer", example=45)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     */
    public function getOnlyTrashed()
    {
        try {
            $data = $this->taskRepository->getOnlyTrashed()->paginate();
            return $this->successResponse(new TaskCollection($data));
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
     *     path="/admin/tasks/{id}/force",
     *     summary="Permanently delete a task",
     *     description="Permanently remove the specified task from storage",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task permanently deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task deletion failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     */
    public function forceDelete($id)
    {
        try {
            $deleted = $this->taskRepository->forceDelete($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("task::app.tasks.deleted-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.deleted-failed"),
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
     * @OA\Post(
     *     path="/admin/tasks/{id}/restore",
     *     summary="Restore a trashed task",
     *     description="Restore a soft-deleted task",
     *     tags={"Admin Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task restored successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Task restoration failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task restoration failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient permissions")
     *         )
     *     )
     * )
     */
    public function restore($id)
    {
        try {
            $restored = $this->taskRepository->restore($id);
            if ($restored) {
                return $this->messageResponse(
                    __("task::app.tasks.restored-successfully"),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __("task::app.tasks.restored-failed"),
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
    /***********Trashed model SoftDeletes**************/

}