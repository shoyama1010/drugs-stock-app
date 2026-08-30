<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  public function test_staff_cannot_access_staff_management(): void
  {
    $this->seed();

    // スタッフとしてログイン
    $login = $this->postJson('/api/login', [
      'employee_code' => '1001',
      'pin' => '1234',
    ]);

    $login->assertStatus(200);

    $token = $login['token'];

    // スタッフ管理一覧へアクセス
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->getJson('/api/staffs');

    // staffは管理者機能を利用できない
    $response->assertStatus(403);
  }

  public function test_staff_cannot_create_staff(): void
  {
    // このテストではユーザー情報だけ必要
    $this->seed(\Database\Seeders\UserSeeder::class);

    // スタッフとしてログイン
    $login = $this->postJson('/api/login', [
      'employee_code' => '1001',
      'pin' => '1234',
    ]);

    $login->assertStatus(200);

    $token = $login['token'];

    // staffのトークンでスタッフ登録を試みる
    $response = $this->withHeader(
      'Authorization',
      'Bearer ' . $token
    )->postJson('/api/staffs', [
      'name' => 'テストスタッフ',
      'email' => 'newstaff@example.com',
    ]);

    // staffは管理者機能を利用できない
    $response->assertStatus(403);

    // DBにも登録されていないことを確認
    $this->assertDatabaseMissing('users', [
      'email' => 'newstaff@example.com',
    ]);
  }
}
