<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    public function uploadFile($file, string $directory, string $disk = "s3")
    {

        $fileName = $file->hashName();
        $filePath = $directory . '/' . $fileName;
        Storage::disk($disk)->put($filePath, file_get_contents($file));
        return $filePath;
    }

    public function getFileAttribute($filePath, $expirationDays = 6, $disk = 's3')
    {
        $temporaryUrl = Storage::disk($disk)->temporaryUrl(
            $filePath,
            now()->addDays($expirationDays)
        );

        return $temporaryUrl;
    }

    public function deleteFile($filePath, string $disk = "s3")
    {

        Storage::disk($disk)->delete($filePath);
    }
}
