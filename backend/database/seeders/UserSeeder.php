<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            // 管理者
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'employee_code' => null,
                'pin' => null,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // スタッフ
            [
                'name' => 'Staff1',
                'email' => 'staff1@test.com', // 必須
                'password' => Hash::make('dummy'), // 必須（使わない）
                'employee_code' => '1001',
                'pin' => Hash::make('1234'), // ←重要
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
        // User::insert([
        //     // 管理者
        //     [
        //         'name' => 'Admin',
        //         'email' => 'admin@test.com',
        //         'password' => bcrypt('password'),
        //         'role' => 'admin',
        //     ],
        //     // スタッフ
        //     [
        //         'name' => 'Staff1',
        //         'email' => null,
        //         'employee_code' => '1001',
        //         'pin' => '1234',
        //         // 'pin' => Hash::make('1234'),
        //         'role' => 'staff',
        //     ]
        // ]);

}
