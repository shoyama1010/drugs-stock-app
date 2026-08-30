<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockInAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  public function test_unauthenticated_user_cannot_stock_in(): void
  {
    $this->seed();

    $response = $this->postJson('/api/stocks/in', [
      'product_id' => 1,
      'quantity' => 10,
      'lot_number' => 'LOT-AUTH-001',
      'shelf' => 'A-1-01',
      'expiry_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $response->assertStatus(401);
  }
}
