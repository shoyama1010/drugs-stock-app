<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockInDatabaseTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_in_quantity_is_saved_to_database(): void
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

    // 10個入庫
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      // 'product_id' => 1,
      'product_id' => $productId,
      'quantity' => 10,
      'lot_number' => 'LOT-DB-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $response->assertStatus(200);

    // 入庫時に作成されたロットIDを取得
    $lotId = $response->json('lot_id');

    // 入庫直後は「初期数量10」「残数量10」であることを確認
    $this->assertDatabaseHas('stock_lot_locations', [
      'stock_lot_id' => $lotId,
      // 'location_id' => 1,
      'location_id' => $locationId,
      'quantity_initial' => 10,
      'quantity_remaining' => 10,
    ]);
  }
}
