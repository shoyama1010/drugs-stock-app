<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_staff_can_login(): void
    {
        $this->seed();

        $response = $this->postJson('/api/login', [
            'employee_code' => '1001',
            'pin' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'role',
                'user',
            ]);
    }

    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }
}
