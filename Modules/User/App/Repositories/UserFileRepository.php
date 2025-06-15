<?php

namespace Modules\User\App\Repositories;

use App\Traits\HandlesUploadsTrait;
use Modules\User\App\Models\UserFile;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\DB;

class UserFileRepository extends BaseRepository
{
    use HandlesUploadsTrait;
    public function model()
    {
        return UserFile::class;
    }
    public function getByUserId($userId)
    {
        return $this->model
            ->where('user_id', $userId);
    }
    public function createOne(array $data)
    {

        try {
            DB::beginTransaction();
            $data['file'] = $this->handleFileUpload('file', UserFile::FILES_DIRECTORY);
            $data['type'] = $this->getFileType(request()->file('file'));
            $created = $this->create($data);
            DB::commit();
            return $created;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function updateOne(array $data, int $id)
    {
        try {
            DB::beginTransaction();
            $userFile = $this->model->findOrFail($id);
            if (request()->hasFile('file')) {
                $data['file'] = $this->handleFileUpload('file', UserFile::FILES_DIRECTORY,$userFile->file);
                $data['type'] = $this->getFileType(request()->file('file'));
            }
            $updated = $userFile->update($data);

            DB::commit();

            return $userFile->refresh();
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function deleteOne(int $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $userFile = $this->model->where('user_id', $userId)->findOrFail($id);
            if ($userFile->file) {
                $this->deleteFile($userFile->file);
            }
            $deleted = $userFile->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function getFileType($file)
    {
        if (in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'])) {
            return UserFile::TYPE_IMAGE;
        } elseif (in_array($file->getClientOriginalExtension(), ['pdf'])) {
            return UserFile::TYPE_FILE_PDF;
        } elseif (in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            return UserFile::TYPE_FILE_DOC;
        }
    }
}
