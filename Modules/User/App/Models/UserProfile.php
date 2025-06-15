<?php

namespace Modules\User\App\Models;

use App\Traits\UploadFileTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use EloquentFilter\Filterable;
use Modules\User\Database\Factories\UserProfileFactory;

class UserProfile extends Model
{
    use HasFactory;
    use Filterable;
    use UploadFileTrait;


    protected $table = 'user_profiles';




    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bio',
        'language',
        'mode',
        'sound_effects',
        'gender',
        'birth_date',
        'user_id',
        'send_email_notifications',
        'allow_related_notifications',

    ];


    public $timestamps = false;


    /**
     * Get the admins.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   

}