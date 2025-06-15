<?php

namespace Modules\User\App\Repositories;

use Modules\User\App\Models\FcmToken;
use Prettus\Repository\Eloquent\BaseRepository;

class UserFcmTokenRepository extends BaseRepository
{
    public function model()
    {
        return FcmToken::class;
    }
}
