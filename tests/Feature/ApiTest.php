<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Test store officers API endpoint requires admin auth.
     */
    public function test_store_officers_api_requires_admin(): void
    {
        // Unauthenticated access
        $response = $this->get('/api/admin/store-officers');
        $response->assertRedirect(route('login'));

        // Authenticated non-admin access should return 403
        $user = User::factory()->create(['is_admin' => false]);
        $response2 = $this->actingAs($user)->get('/api/admin/store-officers');
        $response2->assertStatus(403);
    }

    public function test_store_officers_api_as_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/api/admin/store-officers');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'users']);
    }

    public function test_total_unread_excludes_requisition_status_messages_and_includes_in_approvals_count(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();

        // 1. A standard message
        \App\Models\Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'message' => 'Hello there, regular chat!',
            'read_at' => null,
            'is_automated' => false,
        ]);

        // 2. An item approval status notification message
        \App\Models\Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'message' => "<div class='personnel-view requisition-status-msg'>Approved!</div>",
            'read_at' => null,
            'is_automated' => true,
        ]);

        $response = $this->actingAs($user)->get('/api/total-unread');

        $response->assertStatus(200)
                 ->assertJson([
                     'count' => 1,
                     'approvals_count' => 1,
                     'requested_approvals_count' => 0
                 ]);
    }

    public function test_admin_show_route_authorizations(): void
    {
        $requester = User::factory()->create(['role' => 'Requisitioner', 'department' => 'Intelligence Department', 'registration_status' => 'approved']);
        $deptHead = User::factory()->create(['role' => 'Department Head', 'department' => 'Intelligence Department', 'registration_status' => 'approved']);
        $unauthorizedUser = User::factory()->create(['role' => 'Requisitioner', 'department' => 'Welfare Department', 'registration_status' => 'approved']);
        $admin = User::factory()->create(['is_admin' => true, 'registration_status' => 'approved']);

        $requisition = \App\Models\StoreRequisition::create([
            'requester_name' => $requester->name,
            'department' => 'Intelligence Department',
            'requested_by' => $requester->id,
            'purpose' => 'Test Requisition',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
        ]);

        // 1. Admin should be authorized
        $this->actingAs($admin)->get("/admin/requisitions/{$requisition->id}/show")->assertStatus(200);

        // 2. Original Requester should be authorized
        $this->actingAs($requester)->get("/admin/requisitions/{$requisition->id}/show")->assertStatus(200);

        // 3. Same Department Head should be authorized
        $this->actingAs($deptHead)->get("/admin/requisitions/{$requisition->id}/show")->assertStatus(200);

        // 4. Unauthorized User should be forbidden (403)
        $this->actingAs($unauthorizedUser)->get("/admin/requisitions/{$requisition->id}/show")->assertStatus(403);
    }

    public function test_admin_process_reduced_quantities_approves_directly_partially(): void
    {
        // Hot-patch database columns in sqlite memory for testing
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
        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'alternative_status')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('alternative_status')->nullable();
            });
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisition_items', 'alternative_description')) {
            \Illuminate\Support\Facades\Schema::table('store_requisition_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('alternative_description')->nullable();
            });
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisition_items', 'alternative_quantity_approved')) {
            \Illuminate\Support\Facades\Schema::table('store_requisition_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->decimal('alternative_quantity_approved', 15, 2)->nullable();
            });
        }

        $admin = User::factory()->create(['is_admin' => true, 'registration_status' => 'approved']);
        $requester = User::factory()->create(['role' => 'Requisitioner', 'department' => 'Intelligence Department', 'registration_status' => 'approved']);

        $requisition = \App\Models\StoreRequisition::create([
            'requester_name' => $requester->name,
            'department' => 'Intelligence Department',
            'requested_by' => $requester->id,
            'purpose' => 'Test Requisition',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
        ]);
        $requisition->main_admin_status = 'approved';
        $requisition->origin_admin_status = 'approved';
        $requisition->save();

        $item = \App\Models\StoreRequisitionItem::create([
            'requisition_id' => $requisition->id,
            'description' => 'PENCIL',
            'category' => 'A',
            'unit' => 'PIECE(S)',
            'quantity_requested' => 10,
        ]);

        // Create inventory stock so it is sufficient
        $batch = \App\Models\InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'PENCIL',
            'stock_balance' => '50',
            'unit' => 'PIECE(S)',
        ]);

        // Call the admin process endpoint with reduced quantity (e.g. 5 approved instead of 10 requested)
        $response = $this->actingAs($admin)->post("/admin/requisitions/{$requisition->id}/process", [
            'status' => 'partially_approved',
            'alternative_status' => null,
            'admin_notes' => 'Reduced due to stock control policies.',
            'decline_reason' => null,
            'items' => [
                [
                    'id' => $item->id,
                    'quantity_approved' => 5,
                    'alternative_description' => null,
                    'alternative_quantity_approved' => 0,
                    'remarks' => 'Reduced',
                ]
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        $this->assertEquals('partially_approved', $requisition->status);
        $this->assertNull($requisition->alternative_status);

        $item->refresh();
        $this->assertEquals(5.0, (float) $item->quantity_approved);
    }
}
