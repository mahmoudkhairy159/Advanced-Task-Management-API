<?php

namespace Modules\Task\App\Http\Controllers\Api;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Task\App\Http\Requests\Api\Task\UpdateTaskStatusRequest;
use Modules\Task\App\Http\Requests\Api\Task\StoreTaskRequest;
use Modules\Task\App\Http\Requests\Api\Task\UpdateTaskRequest;
use Modules\Task\App\Repositories\TaskRepository;
use Modules\Task\App\Transformers\Admin\Task\TaskResource;
use Modules\Task\App\Transformers\Api\Task\TaskCollection;
use Modules\User\App\Models\User;
use OpenApi\Attributes as OA;
class TaskController extends Controller
{
    use ApiResponseTrait;


    protected $taskRepository;

    protected $_config;
    protected $guard;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->guard = 'user-api';

        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->taskRepository = $taskRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     summary="Get all tasks",
     *     description="Retrieve a paginated list of tasks assigned to the authenticated user",
     *     operationId="getTasks",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by task status",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by task priority",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
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
            request()->merge(['assignable_type' => User::class]);
            $data = $this->taskRepository->getAll()->paginate();
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
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     summary="Create a new task",
     *     description="Create a new task with the provided details. Rate limited to prevent spam.",
     *     operationId="createTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task data",
     *         @OA\JsonContent(ref="#/components/schemas/CreateTaskRequest")
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests - rate limit exceeded",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many attempts. Please try again later.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
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
     *     path="/api/v1/tasks/{id}",
     *     summary="Get a specific task",
     *     description="Retrieve details of a specific task by ID. Only accessible to users who can view the task.",
     *     operationId="getTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
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

            $data = $this->taskRepository->getOneById($id);
            if (!$data || $data->assignable_type !== User::class) {
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
     *     path="/api/v1/tasks/{id}",
     *     summary="Update a task",
     *     description="Update an existing task with new details. Only authorized users can update tasks.",
     *     operationId="updateTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated task data",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTaskRequest")
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized to update this task",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Not authorized to update this task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
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
                return $this->errorResponse(
                    __("task::app.tasks.not-authorized-to-update"),
                    403
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
     *     path="/api/v1/tasks/{id}/status",
     *     summary="Update task status",
     *     description="Update the status of a specific task. Can be used to mark tasks as pending, in progress, completed, or overdue.",
     *     operationId="updateTaskStatus",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task status update data",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTaskStatusRequest")
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
     *         description="Status update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Not authorized to update this task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
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
                    __("task::app.tasks.not-authorized-to-update"),
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
     *     path="/api/v1/tasks/{id}",
     *     summary="Delete a task (soft delete)",
     *     description="Soft delete a task. The task will be moved to trash and can be restored later.",
     *     operationId="deleteTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
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
     *         description="Deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task deletion failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
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
            $deleted = $this->taskRepository->deleteOne($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("task::app.tasks.deleted-successfully"),
                    true,
                    200
                );
            } else {
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
     * @OA\Get(
     *     path="/api/v1/tasks/trashed",
     *     summary="Get trashed tasks",
     *     description="Retrieve a list of soft-deleted tasks that can be restored.",
     *     operationId="getTrashedTasks",
     *     tags={"Tasks"},
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
     *         description="Trashed tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
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
     *     path="/api/v1/tasks/force-delete/{id}",
     *     summary="Permanently delete a task",
     *     description="Permanently delete a task from the database. This action cannot be undone.",
     *     operationId="forceDeleteTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task permanently deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Permanent deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task permanent deletion failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function forceDelete($id)
    {
        try {
            $deleted = $this->taskRepository->forceDeleteOne($id);
            if ($deleted) {
                return $this->messageResponse(
                    __("task::app.tasks.permanent-deleted-successfully"),
                    true,
                    200
                );
            } else {
                return $this->messageResponse(
                    __("task::app.tasks.permanent-deleted-failed"),
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
     *     path="/api/v1/tasks/restore/{id}",
     *     summary="Restore a trashed task",
     *     description="Restore a soft-deleted task back to active status.",
     *     operationId="restoreTask",
     *     tags={"Tasks"},
     *     security={{"jwt":{}}},
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
     *         description="Restore failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task restore failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     )
     * )
     */
    public function restore($id)
    {
        try {
            $restored = $this->taskRepository->restoreOne($id);
            if ($restored) {
                return $this->messageResponse(
                    __("task::app.tasks.restored-successfully"),
                    true,
                    200
                );
            } else {
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
}
