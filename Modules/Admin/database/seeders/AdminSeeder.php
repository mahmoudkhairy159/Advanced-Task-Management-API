<?php

namespace Modules\Admin\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

       Admin::create([
            'name'       => 'Super Admin',
            'email'      => 'admin@gmail.com',
            'password'   => '12345678',
            'status'     => 1,
            'role_id'    => 1,
        ]);


        Admin::create([
            'name'       => 'Mahmoud Khairy',
            'email'      => 'mahmoudkhairy159@gmail.com',
            'password'   => '12345678',
            'status'     => 1,
            'role_id'    => 1,
        ]);
    }
}