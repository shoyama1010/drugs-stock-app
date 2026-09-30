<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'store_code' => 'STORE-001',
                'name' => '中央店',
                'address' => '東京都中央区',
                'phone' => '03-0000-0001',
            ],
            [
                'store_code' => 'STORE-002',
                'name' => '西口店',
                'address' => '東京都新宿区',
                'phone' => '03-0000-0002',
            ],
            [
                'store_code' => 'STORE-003',
                'name' => '駅前店',
                'address' => '東京都渋谷区',
                'phone' => '03-0000-0003',
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(
                ['store_code' => $store['store_code']],
                $store
            );
        }
    }
}
