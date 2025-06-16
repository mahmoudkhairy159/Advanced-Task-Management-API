<?php

declare(strict_types=1);

namespace Modules\Task\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Repositories\TaskRepository;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\User\App\Models\User;
use Mockery;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected TaskRepository $taskRepository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'Modules/Task/database/migrations']);

        $this->taskRepository = new TaskRepository(app());
        $this->user = User::factory()->create();

        Auth::shouldReceive('id')->andReturn($this->user->id);
        Auth::shouldReceive('user')->andReturn($this->user);
    }

    /** @test */
    public function it_returns_correct_model(): void
    {
        $this->assertEquals(Task::class, $this->taskRepository->model());
    }

    /** @test */
    public function it_gets_all_tasks_with_relationships(): void
    {
        $assignedUser = User::factory()->create();
        $creator = User::factory()->create();

        Task::factory()->create([
            'assignable_id' => $assignedUser->id,
            'assignable_type' => User::class,
            'creator_id' => $creator->id,
            'creator_type' => User::class,
        ]);

        $result = $this->taskRepository->getAll();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    /** @test */
    public function it_gets_one_task_by_id_with_relationships(): void
    {
        $assignedUser = User::factory()->create();
        $creator = User::factory()->create();

        $task = Task::factory()->create([
            'assignable_id' => $assignedUser->id,
            'assignable_type' => User::class,
            'creator_id' => $creator->id,
            'creator_type' => User::class,
        ]);

        $result = $this->taskRepository->getOneById($task->id);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($task->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_task_not_found_by_id(): void
    {
        $result = $this->taskRepository->getOneById(999999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_creates_a_task_successfully(): void
    {
        $data = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'due_date' => now()->addDays(7),
            'priority' => TaskPriorityEnum::PRIORITY_HIGH,
            'status' => TaskStatusEnum::STATUS_PENDING,
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ];

        $result = $this->taskRepository->createOne($data);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Test Task', $result->title);
        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    /** @test */
    public function it_returns_false_when_task_creation_fails(): void
    {
        // Mock database to throw exception
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Provide invalid data that would cause an exception
        $data = [
            'title' => null, // Required field is null
        ];

        $result = $this->taskRepository->createOne($data);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_updates_a_task_successfully_for_assignee(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'assignable_id' => $this->user->id,
            'assignable_type' => User::class,
        ]);

        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $result = $this->taskRepository->updateOne($data, $task->id);

        $this->assertTrue($result);
        $this->assertEquals('Updated Title', $task->fresh()->title);
    }

    /** @test */
    public function it_updates_a_task_successfully_for_creator(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $result = $this->taskRepository->updateOne($data, $task->id);

        $this->assertTrue($result);
        $this->assertEquals('Updated Title', $task->fresh()->title);
    }

    /** @test */
    public function it_returns_false_when_update_fails_due_to_unauthorized_user(): void
    {
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'assignable_id' => $otherUser->id,
            'assignable_type' => User::class,
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
        ]);

        $data = ['title' => 'Updated Title'];

        $result = $this->taskRepository->updateOne($data, $task->id);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_updates_task_status_successfully(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatusEnum::STATUS_PENDING,
        ]);

        $data = ['status' => TaskStatusEnum::STATUS_COMPLETED];

        $result = $this->taskRepository->updateStatus($data, $task);

        $this->assertTrue($result);
        $this->assertEquals(TaskStatusEnum::STATUS_COMPLETED, $task->fresh()->status);
    }

    /** @test */
    public function it_returns_false_when_status_update_fails(): void
    {
        $task = Task::factory()->create();

        // Mock database to throw exception
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $data = ['status' => 'invalid_status'];

        $result = $this->taskRepository->updateStatus($data, $task);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_deletes_a_task_successfully_for_creator(): void
    {
        $task = Task::factory()->create([
            'creator_id' => $this->user->id,
            'creator_type' => User::class,
        ]);

        $result = $this->taskRepository->deleteOne($task->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_returns_false_when_delete_fails_due_to_unauthorized_user(): void
    {
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
        ]);

        $result = $this->taskRepository->deleteOne($task->id);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_false_when_delete_fails_due_to_nonexistent_task(): void
    {
        $result = $this->taskRepository->deleteOne(999999);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_uses_soft_deletable_trait(): void
    {
        $reflection = new \ReflectionClass($this->taskRepository);
        $traits = $reflection->getTraitNames();

        $this->assertContains('App\Traits\SoftDeletableTrait', $traits);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
