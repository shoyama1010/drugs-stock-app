<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'aisle' => 'A',
                'shelf' => '12',
                'position' => '1',
                'capacity' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'aisle' => 'A',
                'shelf' => '01',
                'position' => '02',
                'capacity' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'aisle' => 'B',
                'shelf' => '02',
                'position' => '01',
                'capacity' => 400,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'aisle' => 'C',
                'shelf' => '03',
                'position' => '01',
                'capacity' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
