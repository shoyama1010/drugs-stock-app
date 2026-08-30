<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_success(): void
    {
        $this->seed();

        // 実際にSeederで作られた商品IDを取得
        $productId = DB::table('products')
            ->where('code', 'P0001')
            ->value('id');

        // adminログイン
        $login = $this->postJson('/api/login', [
            // 'email' => 'test@example.com',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $token = $login['token'];

        // 入庫実行
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/stocks/in', [
                // 'product_id' => 1,
                'product_id' => $productId,
                'quantity' => 10,
                'lot_number' => 'LOT-TEST-001',
                'shelf' => 'A-1-01',
                'expiry_date' => now()->addYear()->format('Y-m-d'), // 任意
            ]);

        $response->assertStatus(200);
    }
}
