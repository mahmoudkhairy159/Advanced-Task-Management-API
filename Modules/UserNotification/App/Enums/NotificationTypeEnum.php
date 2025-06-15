<?php

namespace Modules\UserNotification\App\Enums;

use Spatie\Enum\Enum;

final class NotificationTypeEnum extends Enum
{
    //LIKES
    const PROJECT_LIKED = 'PROJECT_LIKED';
    //LIKES
    //COMMENT
    const PROJECT_COMMENTED = 'PROJECT_COMMENTED';
    //COMMENT

    //COMMENT_REPLIED
    const PROJECT_COMMENT_REPLIED = 'PROJECT_COMMENT_REPLIED';
    //COMMENT_REPLIED

    //CASTING
    const VOTE_CASTED = 'VOTE_CASTED';
    const SURVEY_CASTED = 'SURVEY_CASTED';
    //CASTING






    public static function getConstants(): array
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getConstants();
    }
}
