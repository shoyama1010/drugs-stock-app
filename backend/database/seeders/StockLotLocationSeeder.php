<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockLotLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stock_lot_locations')->insert([

            [
                'stock_lot_id' => 1,
                'location_id' => 1,
                // 'quantity' => 120,
                'quantity_initial' => 120,
                'quantity_remaining' => 120,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'stock_lot_id' => 2,
                'location_id' => 2,
                // 'quantity' => 90,
                'quantity_initial' => 90,
                'quantity_remaining' => 90,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'stock_lot_id' => 3,
                'location_id' => 3,
                // 'quantity' => 70,
                'quantity_initial' => 70,
                'quantity_remaining' => 70,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}
