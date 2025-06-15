<?php

namespace Modules\Task\App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Filters\TaskFilter;
use Modules\User\App\Models\User;
use Modules\Admin\App\Models\Admin;

class Task extends Model
{
    use HasFactory, Filterable, SoftDeletes;

    protected $table = 'tasks';

    // Directory for file uploads
    public const FILES_DIRECTORY = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'assignable_type',
        'assignable_id',
        'creator_type',
        'creator_id',
        'updater_type',
        'updater_id',
    ];

    /************************************* Query Scopes **********************************************/

    /**
     * Scope a query to only include active tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', TaskStatusEnum::STATUS_PENDING);
    }
    public function scopeInProgress($query)
    {
        return $query->where('status', TaskStatusEnum::STATUS_IN_PROGRESS);
    }
    public function scopeCompleted($query)
    {
        return $query->where('status', TaskStatusEnum::STATUS_COMPLETED);
    }
    public function scopeOverdue($query)
    {
        return $query->where('status', TaskStatusEnum::STATUS_OVERDUE);
    }
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /************************************* Relationships *********************************************/

    /**
     * Get the assignable model (User or Admin).
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the creator model (User or Admin).
     */
    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the updater model (User or Admin).
     */
    public function updater(): MorphTo
    {
        return $this->morphTo();
    }

    /************************************* Accessors *************************************************/

    /**
     * Get the full URL for the article's image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? $this->getFileAttribute($this->image) : null;
    }

    /**
     * Get the full URL for the article's flag image.
     */


    /************************************* Filtering *************************************************/

    /**
     * Provide the model filter.
     */
    public function modelFilter()
    {
        return $this->provideFilter(TaskFilter::class);
    }
}