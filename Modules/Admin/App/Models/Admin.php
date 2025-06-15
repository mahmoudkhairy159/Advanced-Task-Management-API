<?php

namespace Modules\Admin\App\Models;

use App\Traits\UploadFileTrait;
use EloquentFilter\Filterable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Modules\Admin\App\Filters\AdminFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;


class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Filterable, UploadFileTrait;

    protected $table = 'admins';

    const FILES_DIRECTORY = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'image',
        'password',
        'role_id',
        'status',
        'blocked',
        'password_updated_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /********************************** JWT Authentication ***************************************/

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /********************************** End JWT Authentication ***********************************/

    /********************************** Filterable ***********************************************/

     public function modelFilter()
    {
        return $this->provideFilter(AdminFilter::class);
    }

    /********************************** End Filterable *******************************************/

    /********************************** Image Handling *******************************************/

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Accessor for the image URL.
     *
     * @return string|null
     */
    protected function getImageUrlAttribute()
    {
        return $this->image ? $this->getFileAttribute($this->image) :null;
    }

    /********************************** End Image Handling **************************************/

    /********************************** Mutators *************************************************/

    /**
     * Set the password attribute with hashing.
     *
     * @param string $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /********************************** End Mutators *********************************************/


    /********************************** Relationships ********************************************/

    /**
     * Get the role associated with the admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    /**
     * Get permissions associated with the admin's role.
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissions()
    {
        return collect($this->role->permissions);
    }

    /**
     * Check if the admin has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {

        if ($this->role->permission_type == Role::PERMISSION_TYPE_ALL) {
            return true;
        }

        if ($this->role->permission_type == Role::PERMISSION_TYPE_CUSTOM && !$this->role->permissions) {
            return false;
        }

        return in_array($permission, $this->role->permissions);
    }


}
