<?php
return [
    "tasks" => [
        "name" => __('task::app.tasks.tasks'),
        "sort" => 13,
        "permissions" =>  [
            [
                'key' => 'tasks.show',
                'name' => __('task::app.tasks.show'),
            ],
            [
                'key' => 'tasks.create',
                'name' => __('task::app.tasks.create'),
            ],
            [
                'key' => 'tasks.update',
                'name' => __('task::app.tasks.update'),
            ],
            [
                'key' => 'tasks.destroy',
                'name' => __('task::app.tasks.destroy'),
            ],
        ]
    ],

];