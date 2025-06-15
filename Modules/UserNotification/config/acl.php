<?php
return [

    "userNotifications" => [
        "name" => __('usernotification::app.userNotifications.userNotifications'),
        "sort" => 24,
        "permissions" =>  [
            [
                'key' => 'userNotifications.show',
                'name' => __('usernotification::app.userNotifications.show'),
            ],
            [
                'key' => 'userNotifications.create',
                'name' => __('usernotification::app.userNotifications.create'),
            ],
            [
                'key' => 'userNotifications.update',
                'name' => __('usernotification::app.userNotifications.update'),
            ],
            [
                'key' => 'userNotifications.destroy',
                'name' => __('usernotification::app.userNotifications.destroy'),
            ],
        ]
        ],



];
