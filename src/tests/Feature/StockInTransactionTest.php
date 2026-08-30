<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockInTransactionTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_in_creates_transaction_history(): void
  {
    $this->seed();

    $productId = DB::table('products')
      ->where('code', 'P0001')
      ->value('id');

    // 管理者ログイン
    $login = $this->postJson('/api/login', [
      'email' => 'admin@example.com',
      'password' => 'password',
    ]);

    $token = $login['token'];

    // 10個入庫
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'quantity' => 10,
      'lot_number' => 'LOT-TRANSACTION-IN-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $response->assertStatus(200);

    // 入庫履歴がtransactionsテーブルに保存されていることを確認
    $this->assertDatabaseHas('transactions', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'type' => 'in',
      'quantity' => 10,
    ]);
  }
}
