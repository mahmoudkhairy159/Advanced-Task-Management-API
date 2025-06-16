<?php

namespace Modules\Task\database\seeders;

use Illuminate\Database\Seeder;

class TaskDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TaskSeeder::class,
        ]);
    }
}