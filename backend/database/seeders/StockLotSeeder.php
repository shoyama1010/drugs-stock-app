<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockLotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stock_lots')->insert([
            [
                'product_id' => 1,
                'lot_number' => 'LOT001',
                // 'quantity_total' => 120,
                'received_at' => now(),
                // 'quantity' => 120,
                // 'quantity_initial' => 120,
                // 'quantity_remaining' => 120,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'product_id' => 2,
                'lot_number' => 'LOT-002',
                // 'quantity_total' => 90,
                'received_at' => now(),
                // 'quantity' => 90,
                // 'quantity_initial' => 90,
                // 'quantity_remaining' => 90,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'product_id' => 3,
                'lot_number' => 'LOT-003',
                // 'quantity_total' => 90,
                'received_at' => now(),
                // 'quantity' => 70,
                // 'quantity_initial' => 70,
                // 'quantity_remaining' => 70,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}
