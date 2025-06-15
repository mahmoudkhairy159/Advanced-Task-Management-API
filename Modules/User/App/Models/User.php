<?php

namespace Modules\User\App\Models;

use App\Traits\UploadFileTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Modules\User\App\Filters\UserFilter;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Cog\Contracts\Ban\Bannable as BannableInterface;
use Cog\Laravel\Ban\Traits\Bannable;
use Modules\UserNotification\App\Models\UserDatabaseNotification;


class User extends Authenticatable implements JWTSubject, MustVerifyEmail, BannableInterface
{
    use HasFactory, Notifiable, Filterable, UploadFileTrait, Sluggable, Bannable;


    protected $table = 'users';

    // Constants
    const FILES_DIRECTORY = 'users';
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const ACTIVE = 1;
    const INACTIVE = 0;

    // Attributes

    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'image',
        'status',
        'active',
        'blocked',
        'password_updated_at',
        'created_by',
        'updated_by',
        'last_login_at',
        'fcm_token'
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    protected $appends = ['image_url'];




    /****************************   File Upload    **********************************************/
    protected function getImageUrlAttribute()
    {
        return $this->image ? $this->getFileAttribute($this->image) : null;
    }
    /**************************** End  File Upload    ********************************************/
    /****************************   JWT Authentication   *****************************************/
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
    /****************************  End JWT Authentication   ***************************************/

    /****************************  Notification  *************************************************/
    public function routeNotificationForFcm($notification)
    {
        return $this->fcmTokens()->pluck('token')->toArray() ?? '';
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }
    /****************************  End Notification   **************************************/

    /****************************  Mutators  *************************************************/
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
    /****************************  End Mutators ********************************************/
    /****************************  Slug *************************************************/
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name', 'id'],
                'separator' => '-',
            ],
        ];
    }
    /****************************  End Slug *************************************************/

    //
    /*********************************Model Filter****************************************** */
    public function modelFilter()
    {
        return $this->provideFilter(UserFilter::class);
    }
    /*********************************End Relationships****************************************** */


    /************************************* Query Scopes **********************************************/

    /**
     * Scope a query to only include active user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)->where('active', self::STATUS_ACTIVE)->withoutBanned();
    }
    public function scopeApplyCommonRelations($query)
    {
        return $query->with([
            'profile',
            'phone',
            'userFiles',
        ]);
    }
    /************************************* End Query Scopes ******************************************/



    /*********************************Relationships****************************************** */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }



    // OTP
    public function otps()
    {
        return $this->hasMany(UserOTP::class, 'user_id');
    }

    public function phone()
    {
        return $this->hasOne(UserPhone::class);
    }

    /*********************************End Relationships*******************************************/


    public function notifications()
    {
        return $this->morphMany(UserDatabaseNotification::class, 'notifiable')->latest();
    }


    public function userFiles()
    {
        return $this->hasMany(UserFile::class);
    }
}