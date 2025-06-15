<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPhone extends Model
{
    use HasFactory;

    protected $table = 'user_phones';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'phone_code',
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
