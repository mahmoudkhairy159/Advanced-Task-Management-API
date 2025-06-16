<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Task\database\seeders\TaskSeeder;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
             UserSeeder::class,
            TaskSeeder::class,

        ]);
    }
}