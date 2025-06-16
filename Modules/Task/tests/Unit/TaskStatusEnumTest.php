<?php

declare(strict_types=1);

namespace Modules\Task\Tests\Unit;

use Tests\TestCase;
use Modules\Task\App\Enums\TaskStatusEnum;

class TaskStatusEnumTest extends TestCase
{
    /** @test */
    public function it_has_correct_status_constants(): void
    {
        $this->assertEquals(0, TaskStatusEnum::STATUS_PENDING);
        $this->assertEquals(1, TaskStatusEnum::STATUS_IN_PROGRESS);
        $this->assertEquals(2, TaskStatusEnum::STATUS_COMPLETED);
        $this->assertEquals(3, TaskStatusEnum::STATUS_OVERDUE);
    }

    /** @test */
    public function it_returns_all_constants(): void
    {
        $constants = TaskStatusEnum::getConstants();

        $expected = [
            'STATUS_PENDING' => 0,
            'STATUS_IN_PROGRESS' => 1,
            'STATUS_COMPLETED' => 2,
            'STATUS_OVERDUE' => 3,
        ];

        $this->assertEquals($expected, $constants);
    }

    /** @test */
    public function it_extends_spatie_enum(): void
    {
        $reflection = new \ReflectionClass(TaskStatusEnum::class);

        $this->assertEquals('Spatie\Enum\Enum', $reflection->getParentClass()->getName());
    }

    /** @test */
    public function it_is_final_class(): void
    {
        $reflection = new \ReflectionClass(TaskStatusEnum::class);

        $this->assertTrue($reflection->isFinal());
    }

    /** @test */
    public function it_has_sequential_values(): void
    {
        $constants = TaskStatusEnum::getConstants();
        $values = array_values($constants);

        // Check if values are sequential starting from 0
        $expectedValues = range(0, count($values) - 1);

        $this->assertEquals($expectedValues, $values);
    }
}