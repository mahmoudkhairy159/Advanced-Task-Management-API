<?php

namespace Modules\Admin\App\Repositories;

use Modules\Admin\App\Models\Role;
use Prettus\Repository\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository
{
    public function model()
    {
        return Role::class;
    }
}
