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
    /**Introduction
    Issues
    Changelog
    FAQ

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