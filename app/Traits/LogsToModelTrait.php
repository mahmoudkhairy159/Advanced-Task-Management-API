<?php

namespace App\Traits;

use Psr\Log\LogLevel;

trait LogsToModelTrait
{
    /**
     * Log a message with a specific level.
     *
     * @param string $levelName
     * @param string $message
     * @param array $context
     * @param array $extra
     * @return static
     */
    public static function log(string $levelName, string $message, array $context = [], array $extra = [], $loggableType=null,$loggableId=null): self
    {
        $levels = [
            LogLevel::DEBUG => 100,
            LogLevel::INFO => 200,
            LogLevel::NOTICE => 300,
            LogLevel::WARNING => 400,
            LogLevel::ERROR => 500,
            LogLevel::CRITICAL => 600,
        ];

        $level = $levels[$levelName] ?? 0;

        // Create a new log record
        return self::create([
            'message' => $message,
            'level' => $level,
            'level_name' => strtoupper($levelName),
            'context' => json_encode($context),
            'extra' => json_encode($extra),
            'loggable_type' => $loggableType,
            'loggable_id' => $loggableId,
        ]);
    }

    /**
     * Log a debug message.
     */
    public static function debug(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::DEBUG, $message, $context, $extra,$loggableType,$loggableId);
    }

    /**
     * Log an info message.
     */
    public static function info(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::INFO, $message, $context, $extra,$loggableType,$loggableId);
    }

    /**
     * Log a notice message.
     */
    public static function notice(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::NOTICE, $message, $context, $extra,$loggableType,$loggableId);
    }

    /**
     * Log a warning message.
     */
    public static function warning(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::WARNING, $message, $context, $extra,$loggableType,$loggableId);
    }

    /**
     * Log an error message.
     */
    public static function error(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::ERROR, $message, $context, $extra,$loggableType,$loggableId);
    }

    /**
     * Log a critical message.
     */
    public static function critical(string $message, array $context = [], array $extra = [],$loggableType=null,$loggableId=null): self
    {
        return self::log(LogLevel::CRITICAL, $message, $context, $extra,$loggableType,$loggableId);
    }
}
