<?php

namespace Modules\Admin\App\Repositories;

use App\Traits\HandlesUploadsTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Admin\App\Models\Admin;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class AdminRepository extends BaseRepository
{
    use HandlesUploadsTrait;
    public function model()
    {
        return Admin::class;
    }
    public function getAll()
    {
        return $this->model
            ->with('role')
            ->filter(request()->all())
            ->orderBy('created_at', 'desc');
    }

    public function updateOne(array $data, $id)
    {
        try {
            DB::beginTransaction();
            $admin = $this->model->findOrFail($id);
            $data['image'] = $this->handleFileUpload('image', Admin::FILES_DIRECTORY, $admin->image);
            $updated = $admin->update($data);

            DB::commit();

            return $admin->refresh();
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    //delete by admin
    public function deleteOne($id)
    {
        DB::beginTransaction();
        try {
            $admin = $this->model->findOrFail($id);

            if ($admin->status === Admin::STATUS_INACTIVE) {
                throw new Exception('Admin is already inactive');
            }

            $admin->update(['status' => Admin::STATUS_INACTIVE]);

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return false;
        }
    }
}