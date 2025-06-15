<?php

namespace Modules\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use EloquentFilter\Filterable;
use Modules\Admin\App\Filters\RolesFilter;

class Role extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'roles';

    const PERMISSION_TYPE_ALL = 'all';
    const PERMISSION_TYPE_CUSTOM = 'custom';

    public function modelFilter()
    {
        return $this->provideFilter(RolesFilter::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'permission_type',
        'permissions',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the admins.
     */
    public function admins()
    {
        return $this->hasMany(Admin::class);
    }
}
