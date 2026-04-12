<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_out_success(): void
    {
        $this->seed();

        // adminログイン
        $login = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $token = $login['token'];

        // 先に入庫して在庫を作る
        $stockIn = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/stocks/in', [
                'product_id' => 1,
                'quantity' => 10,
                'lot_number' => 'LOT-OUT-001',
                'shelf' => 'A-1-01',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
            ]);

        // 出庫
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/stocks/out', [
                'product_id' => 1,
                'location_id' => 1, // ←追加
                'quantity' => 5,
                'reason' => 'テスト出庫',
            ]);

        // $response->dump();
        $response->assertStatus(200)
            ->assertJson([
                'message' => '出庫完了',
            ]);

        // DB確認（これ重要）
        $this->assertDatabaseHas('transactions', [
            'product_id' => 1,
            'type' => 'out',
            'quantity' => 5,
        ]);
        // $response->assertStatus(200);
    }
}
