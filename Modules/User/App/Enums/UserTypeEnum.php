<?php

namespace Modules\User\App\Enums;

use Spatie\Enum\Enum;

final class UserTypeEnum extends Enum
{
    const TYPE_NOMAD = 1;
    const TYPE_CITIZEN = 2;
    const TYPE_MASTER = 3;
    const TYPE_FREEMAN_WOMAN =4;

    public static function getConstants(): array
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getConstants();
    }
}
