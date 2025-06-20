<?php

namespace Modules\Admin\database\seeders;

use Illuminate\Database\Seeder;


class AdminDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminSeeder::class,
        ]);
    }
}