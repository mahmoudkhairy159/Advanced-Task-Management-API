<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [

          
            [
                'name'       => 'Ahmad Fahd',
                'email'      => 'ahmad.fahd@wardlin.com',
                'password'   => '12345678',
                'verified_at'   => '2023-10-07T19:22:09.000000Z',
            ],
            [
                'name'       => 'Ahmed Salama',
                'email'      => 'ahmed.salama@wardlin.com',
                'password'   => '12345678',
                'verified_at'   => '2023-10-07T19:22:09.000000Z',
            ],
            [
                'name'       => 'Mahmoud Khairy',
                'email'      => 'mahmoud.khairy@wardlin.com',
                'password'   => '12345678',
                'verified_at'   => '2023-10-07T19:22:09.000000Z',

            ],
            [
                'name'       => 'Mahad shbeeb',
                'email'      => 'mahad.shbeeb@wardlin.com',
                'password'   => '12345678',
                'verified_at'   => '2023-10-07T19:22:09.000000Z',
            ],

        ];
        foreach ($items as $item) {
            User::Create($item);
        }
    }
}
