<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SoftDeletableTrait
{
    public function getOnlyTrashed()
    {
        return $this->model
            ->onlyTrashed()
            ->filter(request()->all())
            ->orderBy('deleted_at', 'desc');
    }
    public function forceDelete(int $id)
    {
        try {
            DB::beginTransaction();
            $model = $this->model->withTrashed()->findOrFail($id);
            $deleted = $model->forceDelete();
            DB::commit();
            return  $deleted;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function restore(int $id)
    {
        try {
            DB::beginTransaction();
            $model = $this->model->withTrashed()->findOrFail($id);
            $restored = $model->restore();
            DB::commit();
            return  $restored;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
}
