<?php

namespace Modules\Task\App\Repositories;

use App\Traits\SoftDeletableTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Task\App\Models\Task;
use Prettus\Repository\Eloquent\BaseRepository;

class TaskRepository extends BaseRepository
{
    use SoftDeletableTrait;

    public function model()
    {
        return Task::class;
    }

    public function getAll()
    {
        return $this->model
            ->filter(request()->all())
            ->with(['assignable', 'creator', 'updater']);
    }

    public function getOneById($id)
    {
        return $this->model
            ->where('id', operator: $id)
            ->filter(request()->all())
            ->with(['assignable', 'creator', 'updater'])
            ->first();
    }
    public function createOne(array $data)
    {
        try {
            DB::beginTransaction();
            $created = $this->model->create($data);
            DB::commit();

            return $created;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }

    public function updateOne(array $data, $id)
    {
        try {
            DB::beginTransaction();
            $task = $this->model->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('assignable_id', Auth::id())
                        ->where('assignable_type', get_class(Auth::user()));
                })->orWhere(function ($q) {
                    $q->where('creator_id', Auth::id())
                        ->where('creator_type', get_class(Auth::user()));
                });
            })->findOrFail($id);

            $updated = $task->update($data);
            DB::commit();
            return $updated;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function updateStatus(array $data, Task $task)
    {
        try {
            DB::beginTransaction();
            $updated = $task->update(['status' => $data['status']]);
            DB::commit();
            return $updated;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }

    public function deleteOne($id)
    {
        try {
            DB::beginTransaction();

            $task = $this->model->where(function ($q) {
                    $q->where('creator_id', Auth::id())
                        ->where('creator_type', get_class(Auth::user()));
                })->findOrFail($id);
            $deleted = $task->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }






}