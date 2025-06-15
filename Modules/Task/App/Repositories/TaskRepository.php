<?php

namespace Modules\Task\App\Repositories;

use App\Traits\SoftDeletableTrait;
use App\Traits\UploadFileTrait;
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
            ->with(['user']);
    }

    public function getOneById($id)
    {
        return $this->model
            ->where('id', operator: $id)
            ->with(['user'])
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

            $task = $this->model->findOrFail($id);
            $updated = $task->update($data);
            DB::commit();
            return $updated;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function updateStatus(array $data,Task $task)
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

            $task = $this->model->findOrFail($id);
            $deleted = $task->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }






}