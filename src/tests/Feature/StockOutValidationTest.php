<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOutValidationTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_out_quantity_zero_is_rejected(): void
  {
    $this->seed();

    // 管理者ログイン
    $login = $this->postJson('/api/login', [
      'email' => 'admin@example.com',
      'password' => 'password',
    ]);

    $token = $login['token'];

    // 先に在庫を作る
    $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      'product_id' => 1,
      'quantity' => 10,
      'lot_number' => 'LOT-OUT-VALIDATION-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // 数量0で出庫
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/out', [
      'product_id' => 1,
      'location_id' => 1,
      'quantity' => 0,
      'reason' => '数量0テスト',
    ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors([
        'quantity',
      ]);
  }
}
