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
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => Hash::make('password'),
                'employee_code' => null,
                'pin_hash' => null,
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // スタッフ
            [
                'name' => 'Staff1',
                'email' => null,
                'password' => null,
                'employee_code' => '1001',
                'pin_hash' => Hash::make('1234'), // ←重要
                'role' => 'staff',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
