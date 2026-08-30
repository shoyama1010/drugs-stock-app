<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockOutTransactionTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_out_creates_transaction_history(): void
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

    // 管理者ログイン
    $login = $this->postJson('/api/login', [
      'email' => 'admin@example.com',
      'password' => 'password',
    ]);

    $token = $login['token'];

    // 先に10個入庫
    $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'quantity' => 10,
      'lot_number' => 'LOT-TRANSACTION-OUT-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ])->assertStatus(200);

    // 5個出庫
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/out', [
      // 'product_id' => 1,
      'product_id' => $productId,
      // 'location_id' => 1,
      'location_id' => $locationId,
      'quantity' => 5,
      'reason' => '出庫履歴テスト',
    ]);

    $response->assertStatus(200);

    // 出庫履歴が保存されていることを確認
    $this->assertDatabaseHas('transactions', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'type' => 'out',
      'quantity' => 5,
    ]);
  }
}
