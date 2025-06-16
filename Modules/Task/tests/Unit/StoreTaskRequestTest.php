<?php

declare(strict_types=1);

namespace Modules\Task\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Task\App\Http\Requests\Api\Task\StoreTaskRequest;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\User\App\Models\User;

class StoreTaskRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Auth::shouldReceive('id')->andReturn($this->user->id);
    }

    /** @test */
    public function it_has_correct_validation_rules(): void
    {
        $request = new StoreTaskRequest();
        $rules = $request->rules();

        $expectedRules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'priority' => ['nullable', 'in:0,1,2,3'],
            'assignable_id' => ['required', 'exists:users,id'],
            'assignable_type' => ['required', 'string', 'in:' . User::class],
            'creator_id' => ['required', 'exists:users,id'],
            'creator_type' => ['required', 'string', 'in:' . User::class],
            'updater_id' => ['required', 'exists:users,id'],
            'updater_type' => ['required', 'string', 'in:' . User::class],
        ];

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('due_date', $rules);
        $this->assertArrayHasKey('priority', $rules);
        $this->assertArrayHasKey('assignable_id', $rules);
    }

    /** @test */
    public function it_validates_required_title(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'description' => 'Task Description',
            'due_date' => now()->addDays(7)->toDateString(),
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_title_max_length(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => str_repeat('a', 256), // Exceeds max length of 255
            'due_date' => now()->addDays(7)->toDateString(),
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_required_due_date(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_due_date_is_after_today(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'due_date' => now()->subDay()->toDateString(), // Yesterday
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_valid_priority_values(): void
    {
        $assignableUser = User::factory()->create();

        $validPriorities = TaskPriorityEnum::getConstants();

        foreach ($validPriorities as $priority) {
            $data = [
                'title' => 'Test Task',
                'due_date' => now()->addDays(7)->toDateString(),
                'priority' => $priority,
                'assignable_id' => $assignableUser->id,
            ];

            $validator = Validator::make($data, (new StoreTaskRequest())->rules());

            $this->assertFalse($validator->fails(), "Priority {$priority} should be valid");
        }
    }

    /** @test */
    public function it_validates_invalid_priority_values(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => 999, // Invalid priority
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('priority', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_required_assignable_id(): void
    {
        $data = [
            'title' => 'Test Task',
            'due_date' => now()->addDays(7)->toDateString(),
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('assignable_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_assignable_id_exists(): void
    {
        $data = [
            'title' => 'Test Task',
            'due_date' => now()->addDays(7)->toDateString(),
            'assignable_id' => 999999, // Non-existent user
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('assignable_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_allows_valid_data(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'description' => 'Task Description',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => TaskPriorityEnum::PRIORITY_HIGH,
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_authorizes_request(): void
    {
        $request = new StoreTaskRequest();

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_allows_nullable_description(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'description' => null,
            'due_date' => now()->addDays(7)->toDateString(),
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_allows_nullable_priority(): void
    {
        $assignableUser = User::factory()->create();

        $data = [
            'title' => 'Test Task',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => null,
            'assignable_id' => $assignableUser->id,
        ];

        $validator = Validator::make($data, (new StoreTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }
}
