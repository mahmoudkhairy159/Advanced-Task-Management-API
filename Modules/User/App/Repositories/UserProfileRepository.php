<?php

namespace Modules\User\App\Repositories;

use App\Traits\UploadFileTrait;
use Modules\User\App\Models\UserProfile;
use Prettus\Repository\Eloquent\BaseRepository;

class UserProfileRepository extends BaseRepository
{
    use UploadFileTrait;
    public function model()
    {
        return UserProfile::class;
    }

}
