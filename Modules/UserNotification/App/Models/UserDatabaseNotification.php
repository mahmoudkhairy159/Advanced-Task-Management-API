<?php

namespace Modules\UserNotification\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class UserDatabaseNotification extends DatabaseNotification
{
    use HasFactory;

}
