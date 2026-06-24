<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test check-reset-status API endpoint (public).
     */
    public function test_check_reset_status_api(): void
    {
        $response = $this->get('/api/check-reset-status?username=nonexistent_test');

        $response->assertStatus(200)
                 ->assertJson(['rejected' => false]);
    }

    /**
     * Test check-forgot-eligibility API endpoint (public).
     */
    public function test_check_forgot_eligibility_api(): void
    {
        $response = $this->get('/api/check-forgot-eligibility?username=nonexistent_test');

        $response->assertStatus(200)
                 ->assertJson(['eligible' => false]);
    }

    /**
     * Test unit-rules API endpoint (requires auth).
     */
    public function test_unit_rules_api_requires_auth(): void
    {
        // Unauthenticated access should redirect to login
        $response = $this->get('/api/unit-rules');
        $response->assertRedirect(route('login'));
    }

    public function test_unit_rules_api_as_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/api/unit-rules');

        $response->assertStatus(200);
    }

    /**
     * Test user permissions API endpoint (requires auth).
     */
    public function test_user_permissions_api_as_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/api/user/permissions');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'can_generate_reports',
                     'can_add_inventory',
                     'can_operate_logistics',
                 ]);
    }

    /**
     * Test unread counts API endpoint (requires auth).
     */
    public function test_unread_counts_api_as_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/api/unread-counts');

        $response->assertStatus(200);
    }
}
