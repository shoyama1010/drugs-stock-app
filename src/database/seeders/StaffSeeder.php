<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('staffs')->insert([
            [
                'name' => '山田太郎',
                'employee_code' => '1001',
                // 'pin' => '1234',   // 自動ハッシュされる
                'pin_hash' => Hash::make('1234'),
                // 'pin' => '1234',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '佐藤次郎',
                'employee_code' => '1002',
                // 'pin' => '1234',
                'pin_hash' => Hash::make('1111'),
                // 'pin' => '1111',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Staff::create([
        //     'name' => '山田太郎',
        //     'employee_code' => '1001',
        //     'pin' => '1234',   // 自動ハッシュされる
        //     // 'pin' => Hash::make('1234'),
        //     'is_active' => true,
        // ]);

        // Staff::create([
        //     'name' => '佐藤次郎',
        //     'employee_code' => '1002',
        //     'pin' => '1111',
        //     // 'pin' => Hash::make('1111'),
        //     'is_active' => true,
        // ]);
    }
}
