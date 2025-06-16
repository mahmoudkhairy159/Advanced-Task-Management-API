<?php

namespace Modules\User\App\Filters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Schema;

class UserFilter extends ModelFilter
{

    public function search($search)
    {
        return $this->where(function ($q) use ($search) {
            return $q->whereFullText(['name', 'email'], $search);
        });
    }
    public function status($status)
    {
        return $this->where('status', $status);

    }
   



    public function sortBy($field)
    {
        $allowedFields = Schema::getColumnListing('users'); // Get all columns dynamically
        if (in_array($field, $allowedFields)) {
            $direction = request('direction', 'desc'); // Default to DESC
            return $this->orderBy($field, in_array($direction, ['asc', 'desc']) ? $direction : 'desc');
        }

        return $this;
    }
}