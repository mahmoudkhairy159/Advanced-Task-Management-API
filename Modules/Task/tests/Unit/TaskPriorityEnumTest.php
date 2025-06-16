<?php

declare(strict_types=1);

namespace Modules\Task\Tests\Unit;

use Tests\TestCase;
use Modules\Task\App\Enums\TaskPriorityEnum;

class TaskPriorityEnumTest extends TestCase
{
    /** @test */
    public function it_has_correct_priority_constants(): void
    {
        $this->assertEquals(0, TaskPriorityEnum::PRIORITY_LOW);
        $this->assertEquals(1, TaskPriorityEnum::PRIORITY_MEDIUM);
        $this->assertEquals(2, TaskPriorityEnum::PRIORITY_HIGH);
        $this->assertEquals(3, TaskPriorityEnum::PRIORITY_CRITICAL);
    }

    /** @test */
    public function it_returns_all_constants(): void
    {
        $constants = TaskPriorityEnum::getConstants();

        $expected = [
            'PRIORITY_LOW' => 0,
            'PRIORITY_MEDIUM' => 1,
            'PRIORITY_HIGH' => 2,
            'PRIORITY_CRITICAL' => 3,
        ];

        $this->assertEquals($expected, $constants);
    }

    /** @test */
    public function it_extends_spatie_enum(): void
    {
        $reflection = new \ReflectionClass(TaskPriorityEnum::class);

        $this->assertEquals('Spatie\Enum\Enum', $reflection->getParentClass()->getName());
    }

    /** @test */
    public function it_is_final_class(): void
    {
        $reflection = new \ReflectionClass(TaskPriorityEnum::class);

        $this->assertTrue($reflection->isFinal());
    }

    /** @test */
    public function it_has_ascending_priority_values(): void
    {
        // Test that priority values are in ascending order (low to critical)
        $this->assertTrue(TaskPriorityEnum::PRIORITY_LOW < TaskPriorityEnum::PRIORITY_MEDIUM);
        $this->assertTrue(TaskPriorityEnum::PRIORITY_MEDIUM < TaskPriorityEnum::PRIORITY_HIGH);
        $this->assertTrue(TaskPriorityEnum::PRIORITY_HIGH < TaskPriorityEnum::PRIORITY_CRITICAL);
    }

    /** @test */
    public function it_has_sequential_values(): void
    {
        $constants = TaskPriorityEnum::getConstants();
        $values = array_values($constants);

        // Check if values are sequential starting from 0
        $expectedValues = range(0, count($values) - 1);

        $this->assertEquals($expectedValues, $values);
    }
}
