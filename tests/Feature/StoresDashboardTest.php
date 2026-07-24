<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoresDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_of_stores_can_access_stores_dashboard()
    {
        $user = User::create([
            'name' => 'Head of Stores Test User',
            'username' => 'stores_head_user',
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'is_admin' => false,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/stores/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('stores.dashboard');
        $response->assertSee('Head of Stores');
    }
}
