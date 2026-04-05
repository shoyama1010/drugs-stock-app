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
        $zones = range('A', 'D');     // とりあえず A〜D
        $aisles = range(1, 4);        // 通路 1〜4
        $shelves = range(1, 5);       // 棚 1〜5

        $locations = [];

        foreach ($zones as $zone) {
            foreach ($aisles as $aisle) {
                foreach ($shelves as $shelf) {

                    $locations[] = [
                        'zone' => $zone,
                        'aisle' => (string)$aisle,
                        'shelf' => str_pad($shelf, 2, '0', STR_PAD_LEFT),
                        'position' => '01', // 今は固定
                        'capacity' => 500,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('locations')->insert($locations);

        
    }
}
