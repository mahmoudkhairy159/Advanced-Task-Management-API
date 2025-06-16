<?php
namespace Modules\Task\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Collection;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\User\App\Models\User;
use Carbon\Carbon;

class TaskModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'Modules/Task/database/migrations']);
    }

    /** @test */
    public function it_can_create_a_task_with_required_fields(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'due_date' => now()->addDays(7),
            'assignable_id' => $user->id,
            'assignable_type' => User::class,
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assignable_id' => $user->id,
            'assignable_type' => User::class,
        ]);
    }

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $task = new Task();

        $expected = [
            'title',
            'description',
            'due_date',
            'priority',
            'status',
            'assignable_type',
            'assignable_id',
            'creator_type',
            'creator_id',
            'updater_type',
            'updater_id',
        ];

        $this->assertEquals($expected, $task->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly(): void
    {
        $task = Task::factory()->create([
            'due_date' => '2024-12-25',
            'status' => TaskStatusEnum::STATUS_PENDING,
            'priority' => TaskPriorityEnum::PRIORITY_HIGH,
        ]);

        $this->assertInstanceOf(Carbon::class, $task->due_date);
        $this->assertIsInt($task->status);
        $this->assertIsInt($task->priority);
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $task = Task::factory()->create();

        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
        $this->assertNotNull($task->fresh()->deleted_at);
    }

    /** @test */
    public function it_has_pending_scope(): void
    {
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_PENDING]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_IN_PROGRESS]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_COMPLETED]);

        $pendingTasks = Task::pending()->get();

        $this->assertCount(1, $pendingTasks);
        $this->assertEquals(TaskStatusEnum::STATUS_PENDING, $pendingTasks->first()->status);
    }

    /** @test */
    public function it_has_in_progress_scope(): void
    {
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_PENDING]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_IN_PROGRESS]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_COMPLETED]);

        $inProgressTasks = Task::inProgress()->get();

        $this->assertCount(1, $inProgressTasks);
        $this->assertEquals(TaskStatusEnum::STATUS_IN_PROGRESS, $inProgressTasks->first()->status);
    }

    /** @test */
    public function it_has_completed_scope(): void
    {
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_PENDING]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_IN_PROGRESS]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_COMPLETED]);

        $completedTasks = Task::completed()->get();

        $this->assertCount(1, $completedTasks);
        $this->assertEquals(TaskStatusEnum::STATUS_COMPLETED, $completedTasks->first()->status);
    }

    /** @test */
    public function it_has_overdue_scope(): void
    {
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_PENDING]);
        Task::factory()->create(['status' => TaskStatusEnum::STATUS_OVERDUE]);

        $overdueTasks = Task::overdue()->get();

        $this->assertCount(1, $overdueTasks);
        $this->assertEquals(TaskStatusEnum::STATUS_OVERDUE, $overdueTasks->first()->status);
    }

    /** @test */
    public function it_has_priority_scope(): void
    {
        Task::factory()->create(['priority' => TaskPriorityEnum::PRIORITY_LOW]);
        Task::factory()->create(['priority' => TaskPriorityEnum::PRIORITY_HIGH]);
        Task::factory()->create(['priority' => TaskPriorityEnum::PRIORITY_CRITICAL]);

        $highPriorityTasks = Task::priority(TaskPriorityEnum::PRIORITY_HIGH)->get();

        $this->assertCount(1, $highPriorityTasks);
        $this->assertEquals(TaskPriorityEnum::PRIORITY_HIGH, $highPriorityTasks->first()->priority);
    }

    /** @test */
    public function it_has_due_soon_scope(): void
    {
        // Task due in 12 hours (should be included)
        Task::factory()->create([
            'due_date' => now()->addHours(12),
        ]);

        // Task due in 30 hours (should not be included with default 24 hours)
        Task::factory()->create([
            'due_date' => now()->addHours(30),
        ]);

        // Task already past due (should not be included)
        Task::factory()->create([
            'due_date' => now()->subHours(1),
        ]);

        $dueSoonTasks = Task::dueSoon()->get();

        $this->assertCount(1, $dueSoonTasks);
    }

    /** @test */
    public function it_has_due_soon_scope_with_custom_hours(): void
    {
        // Task due in 12 hours
        Task::factory()->create([
            'due_date' => now()->addHours(12),
        ]);

        // Task due in 30 hours
        Task::factory()->create([
            'due_date' => now()->addHours(30),
        ]);

        $dueSoonTasks = Task::dueSoon(48)->get(); // 48 hours window

        $this->assertCount(2, $dueSoonTasks);
    }

    /** @test */
    public function it_has_assignable_relationship(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'assignable_id' => $user->id,
            'assignable_type' => User::class,
        ]);

        $this->assertInstanceOf(User::class, $task->assignable);
        $this->assertEquals($user->id, $task->assignable->id);
    }

    /** @test */
    public function it_has_creator_relationship(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
        ]);

        $this->assertInstanceOf(User::class, $task->creator);
        $this->assertEquals($creator->id, $task->creator->id);
    }

    /** @test */
    public function it_has_updater_relationship(): void
    {
        $updater = User::factory()->create();
        $task = Task::factory()->create([
            'updater_id' => $updater->id,
            'updater_type' => User::class,
        ]);

        $this->assertInstanceOf(User::class, $task->updater);
        $this->assertEquals($updater->id, $task->updater->id);
    }

    /** @test */
    public function it_has_with_assignable_scope(): void
    {
        $user = User::factory()->create();

        // Task with assignable
        Task::factory()->create([
            'assignable_id' => $user->id,
            'assignable_type' => User::class,
        ]);

        // Task without assignable (orphaned - should not happen in real scenario)
        Task::factory()->create([
            'assignable_id' => 999999,
            'assignable_type' => User::class,
        ]);

        $tasksWithAssignable = Task::withAssignable()->get();

        $this->assertCount(1, $tasksWithAssignable);
    }

    /** @test */
    public function it_returns_null_for_image_url_when_no_image(): void
    {
        $task = Task::factory()->create();

        $this->assertNull($task->image_url);
    }

    /** @test */
    public function it_has_files_directory_constant(): void
    {
        $this->assertEquals('tasks', Task::FILES_DIRECTORY);
    }
}