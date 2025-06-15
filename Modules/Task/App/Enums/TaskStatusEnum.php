<?php

namespace Modules\Task\App\Enums;

use Spatie\Enum\Enum;

final class TaskStatusEnum extends Enum
{
    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_OVERDUE = 3;

    public static function getConstants(): array
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getConstants();
    }
}