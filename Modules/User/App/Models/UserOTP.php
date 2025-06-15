<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOTP extends Model
{


    protected $fillable = [
        'otp', 'user_id', 'expires_at'
    ];
    protected $table = 'user_otps';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
