<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockOutOverQuantityTest extends TestCase
{
  use RefreshDatabase;

  public function test_cannot_stock_out_more_than_available_stock(): void
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

    $login->assertStatus(200);

    $token = $login['token'];

    // 10個入庫
    $stockIn = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      'product_id' => $productId,
      'quantity' => 10,
      'lot_number' => 'LOT-OVER-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $stockIn->assertStatus(200);

    // 作成されたロットIDを取得
    $lotId = $stockIn->json('lot_id');

    // 在庫は10個しかないのに20個出庫しようとする
    $stockOut = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/out', [
      'product_id' => $productId,
      'location_id' => $locationId,
      'quantity' => 20,
      'reason' => '在庫超過テスト',
    ]);

    // 在庫数を超えているため422になることを確認
    $stockOut->assertStatus(422);

    // 出庫に失敗したので、在庫が10個のまま残っていることを確認
    $this->assertDatabaseHas('stock_lot_locations', [
      'stock_lot_id' => $lotId,
      'location_id' => $locationId,
      'quantity_remaining' => 10,
    ]);
  }
}
