<?php

namespace Modules\Task\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\User\App\Models\User;
use Modules\Admin\App\Models\Admin;
use Modules\Task\App\Enums\TaskPriorityEnum;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users and admins for relationships
        $users = User::active()->take(2)->get();
        $admins = Admin::active()->take(2)->get();

        if ($users->isEmpty() || $admins->isEmpty()) {
            return; // Exit if no users or admins found
        }

        $tasks = [
            // Task created by admin, assigned to user
            [
                'title' => 'Implement User Authentication',
                'description' => 'Set up JWT authentication for user module',
                'due_date' => now()->addDays(7),
                'status' => TaskStatusEnum::STATUS_PENDING,
                'priority' => TaskPriorityEnum::PRIORITY_LOW,
                'creator_type' => Admin::class,
                'creator_id' => $admins[0]->id,
                'assignable_type' => User::class,
                'assignable_id' => $users[0]->id,
                'updater_type' => Admin::class,
                'updater_id' => $admins[0]->id,
            ],
            // Task created by user, assigned to admin
            [
                'title' => 'Review API Documentation',
                'description' => 'Review and update API documentation for all endpoints',
                'due_date' => now()->addDays(5),
                'status' => TaskStatusEnum::STATUS_IN_PROGRESS,
                'priority' => TaskPriorityEnum::PRIORITY_MEDIUM,
                'creator_type' => User::class,
                'creator_id' => $users[0]->id,
                'assignable_type' => Admin::class,
                'assignable_id' => $admins[0]->id,
                'updater_type' => User::class,
                'updater_id' => $users[0]->id,
            ],
            // Task created by admin, assigned to admin
            [
                'title' => 'Database Optimization',
                'description' => 'Optimize database queries and add necessary indexes',
                'due_date' => now()->addDays(10),
                'status' => TaskStatusEnum::STATUS_PENDING,
                'priority' => TaskPriorityEnum::PRIORITY_HIGH,
                'creator_type' => Admin::class,
                'creator_id' => $admins[1]->id,
                'assignable_type' => Admin::class,
                'assignable_id' => $admins[0]->id,
                'updater_type' => Admin::class,
                'updater_id' => $admins[1]->id,
            ],
            // Task created by user, assigned to user
            [
                'title' => 'UI/UX Improvements',
                'description' => 'Implement suggested UI/UX improvements from user feedback',
                'due_date' => now()->addDays(14),
                'status' => TaskStatusEnum::STATUS_COMPLETED,
                'priority' => TaskPriorityEnum::PRIORITY_CRITICAL,
                'creator_type' => User::class,
                'creator_id' => $users[1]->id,
                'assignable_type' => User::class,
                'assignable_id' => $users[0]->id,
                'updater_type' => User::class,
                'updater_id' => $users[1]->id,
            ],
            // Overdue task
            [
                'title' => 'Bug Fixes',
                'description' => 'Fix reported bugs in the authentication system',
                'due_date' => now()->subDays(2),
                'status' => TaskStatusEnum::STATUS_OVERDUE,
                'priority' => TaskPriorityEnum::PRIORITY_CRITICAL,
                'creator_type' => Admin::class,
                'creator_id' => $admins[0]->id,
                'assignable_type' => User::class,
                'assignable_id' => $users[1]->id,
                'updater_type' => Admin::class,
                'updater_id' => $admins[0]->id,
            ],
        ];
        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}