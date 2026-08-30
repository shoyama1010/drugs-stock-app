<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockOutDatabaseTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_out_decreases_remaining_quantity(): void
  {
    $this->seed();

    // Seederで作成された実際の商品IDを取得
    $productId = DB::table('products')
      ->where('code', 'P0001')
      ->value('id');

    // Seederで作成された実際の棚IDを取得
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

    // 10個入庫
    $stockIn = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'quantity' => 10,
      'lot_number' => 'LOT-OUT-DB-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $stockIn->assertStatus(200);

    // 今回作られたロットID
    $lotId = $stockIn->json('lot_id');

    // 5個出庫
    $stockOut = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/out', [
      // 'product_id' => 1,
      // 'location_id' => 1,
      'product_id' => $productId,
      'location_id' => $locationId,
      'quantity' => 5,
      'reason' => '在庫残数テスト',
    ]);

    $stockOut->assertStatus(200);

    // 10個 - 5個 = 残り5個になっていることを確認
    $this->assertDatabaseHas('stock_lot_locations', [
      'stock_lot_id' => $lotId,
      // 'location_id' => 1,
      'location_id' => $locationId,
      'quantity_initial' => 10,
      'quantity_remaining' => 5,
    ]);
  }
}
