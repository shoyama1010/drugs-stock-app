<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOutAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  public function test_unauthenticated_user_cannot_stock_out(): void
  {
    $this->seed();

    $response = $this->postJson('/api/stocks/out', [
      'product_id' => 1,
      'location_id' => 1,
      'quantity' => 5,
      'reason' => '未認証テスト',
    ]);

    $response->assertStatus(401);
  }
}
