<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockInValidationTest extends TestCase
{
  use RefreshDatabase;

  public function test_stock_in_quantity_zero_is_rejected(): void
  {
    $this->seed();

    // 管理者ログイン
    $login = $this->postJson('/api/login', [
      'email' => 'admin@example.com',
      'password' => 'password',
    ]);

    $token = $login['token'];

    // 数量0で入庫
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/stocks/in', [
      'product_id' => 1,
      'quantity' => 0,
      'lot_number' => 'LOT-VALIDATION-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // バリデーションエラー
    $response->assertStatus(422)
      ->assertJsonValidationErrors([
        'quantity',
      ]);
  }
}
