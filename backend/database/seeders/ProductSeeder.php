<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'code' => 'P0001',
                'name' => 'ロキソニンS',
                'sku' => 'MED-001',
                'category_id' => 1,
                'unit_price' => 980,
                'min_stock' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'P0002',
                'name' => 'パブロンゴールドA',
                'sku' => 'MED-002',
                'category_id' => 1,
                'unit_price' => 1200,
                'min_stock' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'P0003',
                'name' => 'ムヒS',
                'sku' => 'MED-003',
                'category_id' => 1,
                'unit_price' => 560,
                'min_stock' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'P0004',
                'name' => 'DHCビタミンC',
                'sku' => 'SUP-001',
                'category_id' => 2,
                'unit_price' => 1000,
                'min_stock' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'P0005',
                'name' => 'マスク50枚入',
                'sku' => 'DLY-003',
                'category_id' => 3,
                'unit_price' => 580,
                'min_stock' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
