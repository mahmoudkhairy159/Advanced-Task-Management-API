<?php
namespace Modules\Task\App\Filters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Schema;

class TaskFilter extends ModelFilter
{

    public function search($search)
    {
        return $this->where(function ($q) use ($search) {
            return $q->whereFullText(['title', 'description'], $search);
        });
    }
    public function status($status)
    {
        return $this->where('status', $status);

    }
    public function dueDate($dateRange)
    {
        return $this->where(function($q) use ($dateRange) {
            if (isset($dateRange['from'])) {
                $q->where('due_date', '>=', $dateRange['from']);
            }
            if (isset($dateRange['to'])) {
                $q->where('due_date', '<=', $dateRange['to']);
            }
        });
    }
    /**
     * Filter tasks by assignable entity
     */
    public function assignable($params)
    {
        return $this->where(function($q) use ($params) {
            if (isset($params['id'])) {
                $q->where('assignable_id', $params['id']);
            }
            if (isset($params['type'])) {
                $q->where('assignable_type', $params['type']);
            }
        });
    }

    /**
     * Filter tasks by creator entity
     */
    public function creator($params)
    {
        return $this->where(function($q) use ($params) {
            if (isset($params['id'])) {
                $q->where('creator_id', $params['id']);
            }
            if (isset($params['type'])) {
                $q->where('creator_type', $params['type']);
            }
        });
    }

    /**
     * Filter tasks by updater entity
     */
    public function updater($params)
    {
        return $this->where(function($q) use ($params) {
            if (isset($params['id'])) {
                $q->where('updater_id', $params['id']);
            }
            if (isset($params['type'])) {
                $q->where('updater_type', $params['type']);
            }
        });
    }

    /**
     * Sort tasks by allowed fields
     * Allowed fields: priority, created_at, due_date, status
     */
    public function sortBy($field)
    {
        $allowedFields = Schema::getColumnListing('tasks'); // Get all columns dynamically
        if (in_array($field, $allowedFields)) {
            $direction = request('direction', 'desc'); // Default to DESC
            return $this->orderBy($field, in_array($direction, ['asc', 'desc']) ? $direction : 'desc');
        }

        return $this;
    }
}