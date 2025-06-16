<?php

declare(strict_types=1);

namespace Modules\Task\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\User\App\Models\User;

class TaskApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected string $apiPrefix = '/api/tasks';

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'Modules/Task/database/migrations']);

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'user-api');
    }

    /** @test */
    public function it_can_list_tasks_for_authenticated_user(): void
    {
        // Create tasks assigned to the authenticated user
        Task::factory()->count(3)->create([
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        // Create a task assigned to another user (should not be visible)
        $otherUser = User::factory()->create();
        Task::factory()->create([
            'assignable_id' => $otherUser->id,
            'assignable_type' => User::class,
        ]);

        $response = $this->getJson($this->apiPrefix);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'due_date',
                            'status',
                            'priority',
                            'assignable',
                            'creator',
                            'updater',
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    /** @test */
    public function it_requires_authentication_to_list_tasks(): void
    {
        Auth::logout();

        $response = $this->getJson($this->apiPrefix);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_a_new_task(): void
    {
        $assignableUser = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task Description',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => TaskPriorityEnum::PRIORITY_HIGH,
            'assignable_id' => $assignableUser->id,
        ];

        $response = $this->postJson($this->apiPrefix, $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.created-successfully"),
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task Description',
            'assignable_id' => $assignableUser->id,
            'assignable_type' => User::class,
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_task(): void
    {
        $response = $this->postJson($this->apiPrefix, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'due_date', 'assignable_id']);
    }

    /** @test */
    public function it_validates_due_date_is_after_today(): void
    {
        $assignableUser = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'due_date' => now()->subDay()->toDateString(), // Yesterday
            'assignable_id' => $assignableUser->id,
        ];

        $response = $this->postJson($this->apiPrefix, $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function it_validates_priority_is_valid_enum_value(): void
    {
        $assignableUser = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => 999, // Invalid priority
            'assignable_id' => $assignableUser->id,
        ];

        $response = $this->postJson($this->apiPrefix, $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function it_validates_assignable_user_exists(): void
    {
        $taskData = [
            'title' => 'New Task',
            'due_date' => now()->addDays(7)->toDateString(),
            'assignable_id' => 999999, // Non-existent user
        ];

        $response = $this->postJson($this->apiPrefix, $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['assignable_id']);
    }

    /** @test */
    public function it_can_show_a_specific_task(): void
    {
        $task = Task::factory()->create([
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        $response = $this->getJson($this->apiPrefix . '/' . $task->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'status',
                    'priority',
                    'assignable',
                    'creator',
                    'updater',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_task(): void
    {
        $response = $this->getJson($this->apiPrefix . '/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('app.data-not-found'),
            ]);
    }

    /** @test */
    public function it_returns_404_for_task_not_assigned_to_user(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'assignable_id' => $otherUser->id,
            'assignable_type' => User::class,
        ]);

        $response = $this->getJson($this->apiPrefix . '/' . $task->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_task_as_assignee(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'due_date' => now()->addDays(14)->toDateString(),
        ];

        $response = $this->putJson($this->apiPrefix . '/' . $task->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.updated-successfully"),
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function it_can_update_a_task_as_creator(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->apiPrefix . '/' . $task->id, $updateData);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_403_when_unauthorized_user_tries_to_update_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'assignable_id' => $otherUser->id,
            'assignable_type' => User::class,
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
        ]);

        $updateData = ['title' => 'Updated Title'];

        $response = $this->putJson($this->apiPrefix . '/' . $task->id, $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_task_status(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatusEnum::STATUS_PENDING,
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        $statusData = [
            'status' => TaskStatusEnum::STATUS_COMPLETED,
        ];

        $response = $this->patchJson($this->apiPrefix . '/' . $task->id . '/status', $statusData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.updated-successfully"),
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatusEnum::STATUS_COMPLETED,
        ]);
    }

    /** @test */
    public function it_validates_status_when_updating(): void
    {
        $task = Task::factory()->create([
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        $statusData = [
            'status' => 999, // Invalid status
        ];

        $response = $this->patchJson($this->apiPrefix . '/' . $task->id . '/status', $statusData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_delete_a_task_as_creator(): void
    {
        $task = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $response = $this->deleteJson($this->apiPrefix . '/' . $task->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.deleted-successfully"),
            ]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_returns_400_when_unauthorized_user_tries_to_delete_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
        ]);

        $response = $this->deleteJson($this->apiPrefix . '/' . $task->id);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __("task::app.tasks.not-authorized-to-delete"),
            ]);
    }

    /** @test */
    public function it_can_get_trashed_tasks(): void
    {
        // Create and delete tasks by the current user
        $task1 = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);
        $task2 = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $task1->delete();
        $task2->delete();

        // Create a trashed task by another user (should not be visible)
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
        ]);
        $otherTask->delete();

        $response = $this->getJson($this->apiPrefix . '/trashed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [],
                    'current_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_can_force_delete_a_task(): void
    {
        $task = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $task->delete(); // Soft delete first

        $response = $this->deleteJson($this->apiPrefix . '/' . $task->id . '/force');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.deleted-successfully"),
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_can_restore_a_trashed_task(): void
    {
        $task = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $task->delete(); // Soft delete first

        $response = $this->postJson($this->apiPrefix . '/' . $task->id . '/restore');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __("task::app.tasks.restored-successfully"),
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
}