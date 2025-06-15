<?php
return [
    "roles" => [
        "name" => __('admin.roles.roles'),
        "sort" => 1,
        "permissions" =>  [
            [
                'key' => 'roles.show',
                'name' => __('admin.roles.show'),
            ],
            [
                'key' => 'roles.create',
                'name' => __('admin.roles.create'),
            ],
            [
                'key' => 'roles.update',
                'name' => __('admin.roles.update'),
            ],
            [
                'key' => 'roles.destroy',
                'name' => __('admin.roles.destroy'),
            ],
        ]
    ],
    "admins" => [
        "name" => __('admin.admins.admins'),
        "sort" => 2,
        "permissions" =>  [
            [
                'key' => 'admins.show',
                'name' => __('admin.admins.show'),
            ],
            [
                'key' => 'admins.create',
                'name' => __('admin.admins.create'),
            ],
            [
                'key' => 'admins.update',
                'name' => __('admin.admins.update'),
            ],
            [
                'key' => 'admins.destroy',
                'name' => __('admin.admins.destroy'),
            ],
        ]
    ]


];
