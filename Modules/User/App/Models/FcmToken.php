<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FcmToken extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = ['user_id', 'token','device_id'];
    protected $table = 'fcm_tokens';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
