<?php

namespace Modules\Admin\App\Filters;

use EloquentFilter\ModelFilter;

class RolesFilter extends ModelFilter
{


    public function search($search)
    {
        return $this->where(function ($q) use ($search) {
            return $q->where('name', 'LIKE', "%$search%");
        });
    }
}
