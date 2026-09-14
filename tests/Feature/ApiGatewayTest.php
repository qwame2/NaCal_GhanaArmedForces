<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'main_admin_status')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'origin_admin_status')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        }
    }

    /**
     * Test API Gateway public endpoint routing without authentication.
     */
    public function test_api_gateway_allows_public_endpoints(): void
    {
        $response = $this->get('/api/check-reset-status?username=nonexistent_test_gateway');

        $response->assertStatus(200)
                 ->assertJson(['rejected' => false]);
    }

    /**
     * Test API Gateway redirects or blocks unauthenticated requests on protected routes.
     */
    public function test_api_gateway_protects_authenticated_routes(): void
    {
        $response = $this->get('/api/unit-rules');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test API Gateway role-based authorization check (403 Forbidden for non-admins).
     */
    public function test_api_gateway_enforces_admin_role_authorization(): void
    {
        $regularUser = User::factory()->create([
            'is_admin' => false,
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
        ]);

        $response = $this->actingAs($regularUser)->get('/api/admin/store-officers');

        $response->assertStatus(403);
    }

    /**
     * Test API Gateway allows access to authorized admin accounts.
     */
    public function test_api_gateway_allows_admin_access(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'Head of Stores',
            'registration_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/api/admin/store-officers');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'users']);
    }

    /**
     * Test API Gateway contract response schema for authenticated user permissions.
     */
    public function test_api_gateway_returns_valid_json_permissions_contract(): void
    {
        $user = User::factory()->create([
            'registration_status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/api/user/permissions');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'can_generate_reports',
                     'can_add_inventory',
                     'can_operate_logistics',
                 ]);
    }
}
