<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Task Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default settings for task notifications including
    | timing, retry attempts, and email templates.
    |
    */

    'due_date' => [
        /*
        |--------------------------------------------------------------------------
        | Default Hours Before Due Date
        |--------------------------------------------------------------------------
        |
        | The default number of hours before a task's due date when a
        | notification should be sent.
        |
        */
        'default_hours' => env('TASK_NOTIFICATION_HOURS', 24),

        /*
        |--------------------------------------------------------------------------
        | Minimum Hours Before Due Date
        |--------------------------------------------------------------------------
        |
        | The minimum number of hours before a task's due date. Notifications
        | will not be sent if the task is due within this timeframe.
        |
        */
        'minimum_hours' => env('TASK_NOTIFICATION_MIN_HOURS', 1),

        /*
        |--------------------------------------------------------------------------
        | Maximum Hours Before Due Date
        |--------------------------------------------------------------------------
        |
        | The maximum number of hours before a task's due date. Notifications
        | will not be sent if the task is due beyond this timeframe.
        |
        */
        'maximum_hours' => env('TASK_NOTIFICATION_MAX_HOURS', 168), // 7 days
    ],

    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Default Queue Name
        |--------------------------------------------------------------------------
        |
        | The default queue name for task notifications.
        |
        */
        'name' => env('TASK_NOTIFICATION_QUEUE', 'notifications'),

        /*
        |--------------------------------------------------------------------------
        | Job Retry Attempts
        |--------------------------------------------------------------------------
        |
        | The number of times to retry failed notification jobs.
        |
        */
        'retries' => env('TASK_NOTIFICATION_RETRIES', 3),

        /*
        |--------------------------------------------------------------------------
        | Job Timeout
        |--------------------------------------------------------------------------
        |
        | The maximum number of seconds a notification job can run.
        |
        */
        'timeout' => env('TASK_NOTIFICATION_TIMEOUT', 60),
    ],

    'email' => [
        /*
        |--------------------------------------------------------------------------
        | Email Template Settings
        |--------------------------------------------------------------------------
        |
        | Configure the email template settings for task notifications.
        |
        */
        'subject_prefix' => env('TASK_EMAIL_SUBJECT_PREFIX', 'Task Reminder'),

        /*
        |--------------------------------------------------------------------------
        | Frontend URL
        |--------------------------------------------------------------------------
        |
        | The base URL for your frontend application to generate task links.
        |
        */
        'frontend_url' => env('FRONTEND_URL', env('APP_URL')),
    ],

    'logging' => [
        /*
        |--------------------------------------------------------------------------
        | Enable Notification Logging
        |--------------------------------------------------------------------------
        |
        | Whether to log notification activities for debugging and monitoring.
        |
        */
        'enabled' => env('TASK_NOTIFICATION_LOGGING', true),

        /*
        |--------------------------------------------------------------------------
        | Log Level
        |--------------------------------------------------------------------------
        |
        | The log level for task notification activities.
        |
        */
        'level' => env('TASK_NOTIFICATION_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    |
    | Configure which types of notifications are enabled.
    |
    */
    'types' => [
        'due_date' => env('TASK_NOTIFICATION_DUE_DATE_ENABLED', true),
        'overdue' => env('TASK_NOTIFICATION_OVERDUE_ENABLED', true),
        'completed' => env('TASK_NOTIFICATION_COMPLETED_ENABLED', false),
        'assigned' => env('TASK_NOTIFICATION_ASSIGNED_ENABLED', false),
    ],

];
