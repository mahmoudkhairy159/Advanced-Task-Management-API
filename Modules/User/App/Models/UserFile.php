<?php

namespace Modules\User\App\Models;

use App\Traits\UploadFileTrait;
use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    use UploadFileTrait;
    protected $fillable = ['user_id', 'type', 'file'];
    public $timestamps = false;
    const FILES_DIRECTORY = 'user_files';
    const TYPE_FILE_PDF = 'pdf';
    const TYPE_FILE_DOC = 'doc';
    const TYPE_IMAGE = 'image';
    protected $appends = ['file_url'];
    protected function getFileUrlAttribute()
    {
        return $this->file ? $this->getFileAttribute($this->file) : null;
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
