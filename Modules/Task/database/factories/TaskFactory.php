<?php
namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\User\App\Models\User;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'status' => $this->faker->randomElement([
                TaskStatusEnum::STATUS_PENDING,
                TaskStatusEnum::STATUS_IN_PROGRESS,
                TaskStatusEnum::STATUS_COMPLETED,
                TaskStatusEnum::STATUS_OVERDUE,
            ]),
            'priority' => $this->faker->randomElement([
                TaskPriorityEnum::PRIORITY_LOW,
                TaskPriorityEnum::PRIORITY_MEDIUM,
                TaskPriorityEnum::PRIORITY_HIGH,
                TaskPriorityEnum::PRIORITY_CRITICAL,
            ]),
            'assignable_type' => User::class,
            'assignable_id' => User::factory(),
            'creator_type' => User::class,
            'creator_id' => User::factory(),
            'updater_type' => User::class,
            'updater_id' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatusEnum::STATUS_PENDING,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatusEnum::STATUS_IN_PROGRESS,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatusEnum::STATUS_COMPLETED,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatusEnum::STATUS_OVERDUE,
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriorityEnum::PRIORITY_LOW,
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriorityEnum::PRIORITY_MEDIUM,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriorityEnum::PRIORITY_HIGH,
        ]);
    }

    public function criticalPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => TaskPriorityEnum::PRIORITY_CRITICAL,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn(array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('+1 hour', '+23 hours'),
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'assignable_type' => User::class,
            'assignable_id' => $user->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'creator_type' => User::class,
            'creator_id' => $user->id,
        ]);
    }
}