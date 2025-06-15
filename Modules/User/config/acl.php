<?php
return [
    "users" => [
        "name" => __('user::app.users.users'),
        "sort" => 3,
        "permissions" =>  [
            [
                'key' => 'users.show',
                'name' => __('user::app.users.show'),
            ],
            [
                'key' => 'users.create',
                'name' => __('user::app.users.create'),
            ],
            [
                'key' => 'users.update',
                'name' => __('user::app.users.update'),
            ],
            [
                'key' => 'users.destroy',
                'name' => __('user::app.users.destroy'),
            ],
        ]
    ],
  


];
