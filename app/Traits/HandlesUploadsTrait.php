<?php

namespace App\Traits;


trait HandlesUploadsTrait
{
    use UploadFileTrait;
    public function handleFileUpload($fileKey, $directory, $existingFile = null)
    {
        if (request()->hasFile($fileKey)) {
            if ($existingFile) {
                $this->deleteFile($existingFile);
            }
            return $this->uploadFile(request()->file($fileKey), $directory);
        }
        return null;
    }
}
