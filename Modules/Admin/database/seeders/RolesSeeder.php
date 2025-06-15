<?php

namespace Modules\Admin\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name'            => 'Super Admin',
            'description'     => 'Administrator role',
            'permission_type' => 'all',
            // 'permissions' => '["admins","settings"]'
        ]);
    }
}
