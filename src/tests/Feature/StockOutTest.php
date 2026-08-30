<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_out_success(): void
    {
        $this->seed();

        $productId = DB::table('products')
            ->where('code', 'P0001')
            ->value('id');

        $locationId = DB::table('locations')
            ->where('zone', 'A')
            ->where('aisle', '1')
            ->where('shelf', '01')
            ->where('position', '01')
            ->value('id');

        // adminログイン
        $login = $this->postJson('/api/login', [
            // 'email' => 'test@test.com',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $token = $login['token'];

        // 先に入庫して在庫を作る
        $stockIn = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/stocks/in', [
                // 'product_id' => 1,
                'product_id' => $productId,
                'quantity' => 10,
                'lot_number' => 'LOT-OUT-001',
                'shelf' => 'A-1-01',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
            ]);

        // 出庫
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/stocks/out', [
            // 'product_id' => 1,
            // 'location_id' => 1, 
            'product_id' => $productId,
            'location_id' => $locationId,
                'quantity' => 5,
                'reason' => 'テスト出庫',
            ]);

        
        $response->assertStatus(200)
            ->assertJson([
                'message' => '出庫完了',
            ]);

        // DB確認（これ重要）
        $this->assertDatabaseHas('transactions', [
            // 'product_id' => 1,
            'product_id' => $productId,
            'type' => 'out',
            'quantity' => 5,
        ]);
        // $response->assertStatus(200);
    }
}
