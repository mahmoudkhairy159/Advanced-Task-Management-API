<?php

namespace Modules\Admin\App\Filters;

use EloquentFilter\ModelFilter;

class AdminFilter extends ModelFilter
{

    public function search($search)
    {
        return $this->where(function ($q) use ($search) {
            $q->whereFullText(['name', 'phone', 'email'], $search);
        });
    }
    public function roleId($roleId)
    {
        return $this->where('role_id', $roleId);
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }
}
