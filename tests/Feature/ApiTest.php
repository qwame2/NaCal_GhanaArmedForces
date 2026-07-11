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

    /**
     * Test that the myRequisitions API is correctly paginated and searchable.
     */
    public function test_my_requisitions_api_is_paginated(): void
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

        $user = User::factory()->create(['role' => 'Requisitioner', 'registration_status' => 'approved']);
        
        // Create 6 requisitions for this user
        for ($i = 1; $i <= 6; $i++) {
            \App\Models\StoreRequisition::create([
                'requester_name' => $user->name,
                'department' => 'IT Department',
                'requested_by' => $user->id,
                'purpose' => "UniqueRequisitionPurpose {$i}",
                'priority' => 'normal',
                'status' => 'pending',
                'usage_type' => 'permanent',
            ]);
        }

        // 1. Get first page
        $response = $this->actingAs($user)->get('/api/my-requisitions?page=1');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'last_page',
                     'per_page',
                     'total',
                     'from',
                     'to'
                 ]);

        $data = $response->json();
        $this->assertCount(5, $data['data']);
        $this->assertEquals(6, $data['total']);
        $this->assertEquals(2, $data['last_page']);
        $this->assertEquals(1, $data['current_page']);

        // 2. Get second page
        $response2 = $this->actingAs($user)->get('/api/my-requisitions?page=2');
        $response2->assertStatus(200);
        $data2 = $response2->json();
        $this->assertCount(1, $data2['data']);
        $this->assertEquals(2, $data2['current_page']);

        // 2b. Custom perPage test (per_page=3)
        $responsePerPage = $this->actingAs($user)->get('/api/my-requisitions?page=1&per_page=3');
        $responsePerPage->assertStatus(200);
        $dataPerPage = $responsePerPage->json();
        $this->assertCount(3, $dataPerPage['data']);
        $this->assertEquals(6, $dataPerPage['total']);
        $this->assertEquals(2, $dataPerPage['last_page']);
        $this->assertEquals(3, $dataPerPage['per_page']);

        // 3. Search filter matching
        $responseSearch = $this->actingAs($user)->get('/api/my-requisitions?search=UniqueRequisitionPurpose+3');
        $responseSearch->assertStatus(200);
        $dataSearch = $responseSearch->json();
        $this->assertEquals(1, $dataSearch['total']);
        $this->assertEquals('UniqueRequisitionPurpose 3', $dataSearch['data'][0]['purpose']);

        // 4. Search filter mismatch
        $responseNoMatch = $this->actingAs($user)->get('/api/my-requisitions?search=NonExistentSearchTerm');
        $responseNoMatch->assertStatus(200);
        $dataNoMatch = $responseNoMatch->json();
        $this->assertEquals(0, $dataNoMatch['total']);
        $this->assertCount(0, $dataNoMatch['data']);
    }

    /**
     * Test that the notification icon is hidden on other department head pages.
     */
    public function test_notification_icon_is_hidden_on_other_dept_head_pages(): void
    {
        // Hot-patch database columns in sqlite memory for testing (ignoring schema cache issues)
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('alternative_status')->nullable();
            });
        } catch (\Exception $e) {}

        // Case 1: Other Department Head (role: Department Head, department: Welfare) on main-admin.requisitions
        $hod = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Welfare',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($hod)->get(route('main-admin.requisitions'));
        $response->assertStatus(200);
        $response->assertDontSee('id="notification-btn"', false);

        // Case 2: Other Department Head on track-requests
        $responseTrack = $this->actingAs($hod)->get(route('main-admin.track-requests'));
        $responseTrack->assertStatus(200);
        $responseTrack->assertDontSee('id="notification-btn"', false);

        // Case 3: Stores Department Head (Main Admin) on main-admin.requisitions (should still see notification btn)
        $storesHead = User::factory()->create([
            'role' => 'Main Admin',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $responseStores = $this->actingAs($storesHead)->get(route('main-admin.requisitions'));
        $responseStores->assertStatus(200);
        $responseStores->assertSee('id="notification-btn"', false);
    }

    /**
     * Test that Store Officer requisitions route only through Head of Stores, excluding Head of Admin.
     */
    public function test_store_officer_requisition_flow_excludes_head_of_admin(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        $headOfStores = User::factory()->create([
            'role' => 'Head of Stores',
            'department' => '',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $mainAdmin = User::factory()->create([
            'role' => 'Main Admin',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $storeOfficer = User::factory()->create([
            'role' => 'Officer',
            'department' => 'Stores',
            'phone' => '0241112222',
            'service_number' => 'SRV123',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // Create stocks
        $admin = User::first() ?? User::factory()->create(['is_admin' => true]);
        $batchA = \App\Models\InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batchA->id,
            'description' => 'Office Pen',
            'stock_balance' => 50,
            'unit' => 'Piece',
        ]);

        $batchS = \App\Models\InventoryBatch::create([
            'ledge_category' => 'S',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batchS->id,
            'description' => 'Special Store Item',
            'stock_balance' => 50,
            'unit' => 'Piece',
        ]);

        // Configure S as a Stores Head approval category
        \App\Models\Setting::updateOrCreate(
            ['key' => 'stores_dept_head_approval_categories'],
            ['value' => json_encode(['S'])]
        );

        // Case 1: Store Officer submits requisition for category 'A' (not in Stores Head approval categories)
        // It should skip all approvals and be directly ready for collection/issuance
        $response1 = $this->actingAs($storeOfficer)->postJson('/requisitions', [
            'requester_name' => $storeOfficer->name,
            'department' => 'Stores',
            'rank_or_title' => 'Officer',
            'purpose' => 'Immediate store use',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $response1->assertStatus(200);

        $req1 = \App\Models\StoreRequisition::where('purpose', 'Immediate store use')->first();
        $this->assertNotNull($req1);
        $this->assertEquals('approved', $req1->origin_admin_status);
        $this->assertEquals('approved', $req1->main_admin_status);

        // Case 2: Store Officer submits requisition for category 'S' (requires Stores Head approval)
        // It starts as origin_admin_status = 'approved', main_admin_status = 'pending'
        $response2 = $this->actingAs($storeOfficer)->postJson('/requisitions', [
            'requester_name' => $storeOfficer->name,
            'department' => 'Stores',
            'rank_or_title' => 'Officer',
            'purpose' => 'Special store use',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Special Store Item',
                    'category' => 'S',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $response2->assertStatus(200);

        $req2 = \App\Models\StoreRequisition::where('purpose', 'Special store use')->first();
        $this->assertNotNull($req2);
        $this->assertEquals('approved', $req2->origin_admin_status);
        $this->assertEquals('pending', $req2->main_admin_status);

        // A. Verify Main Admin does NOT see Case 2 requisition in pending reviews
        $responseMainAdmin = $this->actingAs($mainAdmin)->get(route('main-admin.requisitions'));
        $responseMainAdmin->assertStatus(200);
        $responseMainAdmin->assertDontSee("Special store use");

        // B. Verify Head of Stores DOES see Case 2 requisition in pending reviews (since it is pending main_admin_status)
        $responseHeadOfStores = $this->actingAs($headOfStores)->get(route('main-admin.requisitions'));
        $responseHeadOfStores->assertStatus(200);
        $responseHeadOfStores->assertSee("Special store use");

        // C. Try to process/approve as Main Admin (should be unauthorized/forbidden because they are not the HOD fallback)
        $responseProcessMain = $this->actingAs($mainAdmin)->post(route('main-admin.requisitions.process', $req2->id), [
            'status' => 'approved',
        ]);
        $responseProcessMain->assertStatus(400);

        // D. Head of Stores approves the Stores Head part for Case 2
        $responseProcessHead2Second = $this->actingAs($headOfStores)->post(route('main-admin.requisitions.process', $req2->id), [
            'status' => 'approved',
        ]);
        $responseProcessHead2Second->assertStatus(200);

        $req2->refresh();
        $this->assertEquals('approved', $req2->origin_admin_status);
        $this->assertEquals('approved', $req2->main_admin_status);
    }

    public function test_stores_department_head_as_stores_hod(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        $storesDeptHead = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $storeOfficer = User::factory()->create([
            'role' => 'Officer',
            'department' => 'Stores',
            'phone' => '0241112222',
            'service_number' => 'SRV123',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // Create stocks
        $admin = User::first() ?? User::factory()->create(['is_admin' => true]);
        $batch = \App\Models\InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'Office Pen',
            'stock_balance' => 50,
            'unit' => 'Piece',
        ]);

        // Submit requisition
        $response = $this->actingAs($storeOfficer)->postJson('/requisitions', [
            'requester_name' => $storeOfficer->name,
            'department' => 'Stores',
            'rank_or_title' => 'Officer',
            'purpose' => 'For stores use',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $response->assertStatus(200);

        $req = \App\Models\StoreRequisition::where('purpose', 'For stores use')->first();
        $this->assertNotNull($req);
        $this->assertEquals('approved', $req->origin_admin_status);
        $this->assertEquals('approved', $req->main_admin_status);
    }

    public function test_department_toggling_requisition_blocking(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        // 1. Create a Head of Stores
        $headOfStores = User::factory()->create([
            'role' => 'Head of Stores',
            'department' => '',
            'is_admin' => true,
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 2. Create a Main Admin
        $mainAdmin = User::factory()->create([
            'role' => 'Main Admin',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 3. Create a Requisitioner in Welfare Department
        $requisitioner = User::factory()->create([
            'role' => 'Requisitioner',
            'department' => 'Welfare Department',
            'phone' => '0241112222',
            'service_number' => 'SRV999',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // Clean settings
        \App\Models\Setting::set('disabled_requisition_departments', [], 'json');

        // A. Verify Main Admin (or non-Head of Stores) cannot toggle department status (returns 403)
        $responseToggleAdmin = $this->actingAs($mainAdmin)->postJson(route('admin.permissions.toggle_department'), [
            'department' => 'Welfare Department',
            'value' => false, // false means block it
        ]);
        $responseToggleAdmin->assertStatus(403);

        // B. Verify Head of Stores can toggle department status (returns 200)
        $responseToggleHead = $this->actingAs($headOfStores)->postJson(route('admin.permissions.toggle_department'), [
            'department' => 'Welfare Department',
            'value' => false,
        ]);
        $responseToggleHead->assertStatus(200);

        // C. Verify requisitioner in Welfare Department is now blocked from submitting a requisition
        $responseSubmit = $this->actingAs($requisitioner)->postJson('/requisitions', [
            'requester_name' => $requisitioner->name,
            'department' => 'Welfare Department',
            'rank_or_title' => 'Requisitioner',
            'purpose' => 'Need stationary',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $responseSubmit->assertStatus(403);
        $this->assertStringContainsString('Your department has been disabled', $responseSubmit->json('message'));

        // D. Verify Head of Stores can re-enable the department
        $responseToggleHead2 = $this->actingAs($headOfStores)->postJson(route('admin.permissions.toggle_department'), [
            'department' => 'Welfare Department',
            'value' => true, // true means allow it
        ]);
        $responseToggleHead2->assertStatus(200);

        // E. Create stock item to allow requisition submission
        $admin = User::first() ?? User::factory()->create(['is_admin' => true]);
        $batch = \App\Models\InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'Office Pen',
            'stock_balance' => 50,
            'unit' => 'Piece',
        ]);

        // F. Verify requisitioner in Welfare Department can now submit successfully
        $responseSubmit2 = $this->actingAs($requisitioner)->postJson('/requisitions', [
            'requester_name' => $requisitioner->name,
            'department' => 'Welfare Department',
            'rank_or_title' => 'Requisitioner',
            'purpose' => 'Need stationary',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $responseSubmit2->assertStatus(200);
    }

    public function test_other_department_heads_self_requisition(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        // 1. Create a non-Stores Department Head (Welfare)
        $welfareHOD = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Welfare Department',
            'phone' => '0241113333',
            'service_number' => 'SRV111',
            'registration_status' => 'approved',
            'is_active' => true,
            'can_make_requisition' => false, // Default is false for HODs
        ]);

        // 2. Create a Stores HOD
        $storesHOD = User::factory()->create([
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'phone' => '0241114444',
            'service_number' => 'SRV222',
            'registration_status' => 'approved',
            'is_active' => true,
            'can_make_requisition' => false,
        ]);

        // Create stock item
        $admin = User::first() ?? User::factory()->create(['is_admin' => true]);
        $batch = \App\Models\InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        \App\Models\InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'Office Pen',
            'stock_balance' => 50,
            'unit' => 'Piece',
        ]);

        // A. Verify non-Stores HOD can submit requisition (bypasses can_make_requisition restriction)
        $responseSubmit = $this->actingAs($welfareHOD)->postJson('/requisitions', [
            'requester_name' => $welfareHOD->name,
            'department' => 'Welfare Department',
            'rank_or_title' => 'Department Head',
            'purpose' => 'For office use',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $responseSubmit->assertStatus(200);

        // B. Verify it is automatically set to origin_admin_status = 'approved' and origin_approved_by matches welfareHOD's name
        $req = \App\Models\StoreRequisition::where('requested_by', $welfareHOD->id)->first();
        $this->assertNotNull($req);
        $this->assertEquals('approved', $req->origin_admin_status);
        $this->assertEquals($welfareHOD->name, $req->origin_approved_by);

        // C. Verify Stores HOD with can_make_requisition = false is STILL blocked
        $responseSubmit2 = $this->actingAs($storesHOD)->postJson('/requisitions', [
            'requester_name' => $storesHOD->name,
            'department' => 'Stores',
            'rank_or_title' => 'Head of Stores',
            'purpose' => 'For stores use',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Office Pen',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => '',
                ]
            ]
        ]);
        $responseSubmit2->assertStatus(403);
    }

    public function test_acting_stores_head_as_originating_head(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        // Create Welfare HOD
        $welfareHOD = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Welfare Department',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // Create Welfare Requisition (pending first-tier review)
        $welfareReq = \App\Models\StoreRequisition::create([
            'requester_name' => 'Welfare Staff',
            'department' => 'Welfare Department',
            'purpose' => 'Welfare request',
            'priority' => 'normal',
            'status' => 'pending',
            'origin_admin_status' => 'pending',
            'main_admin_status' => 'pending',
            'usage_type' => 'permanent',
        ]);

        // Create IT Requisition (pending second-tier review, already approved by IT HOD)
        $itReq = \App\Models\StoreRequisition::create([
            'requester_name' => 'IT Staff',
            'department' => 'IT Department',
            'purpose' => 'IT request',
            'priority' => 'normal',
            'status' => 'pending',
            'origin_admin_status' => 'approved',
            'main_admin_status' => 'pending',
            'usage_type' => 'permanent',
        ]);

        // 1. Verify Welfare HOD sees both requisitions in pending reviews (since there are no online Stores Heads, making them acting Stores Head)
        // Ensure no other Main Admin / stores head is online
        User::where(function($q) {
            $q->where('role', 'Main Admin')
              ->orWhere('role', 'Dept. Head (Stores)')
              ->orWhereIn('department', ['Stores', 'Store']);
        })->update(['is_online' => false]);

        $responseView = $this->actingAs($welfareHOD)->get(route('main-admin.requisitions'));
        $responseView->assertStatus(200);
        $responseView->assertSee("Welfare request");
        $responseView->assertSee("IT request");

        // 2. Verify Welfare HOD can approve the Welfare requisition as originating HOD
        $responseApproveWelfare = $this->actingAs($welfareHOD)->post(route('main-admin.requisitions.process', $welfareReq->id), [
            'status' => 'approved',
        ]);
        $responseApproveWelfare->assertStatus(200);

        $welfareReq->refresh();
        $this->assertEquals('approved', $welfareReq->origin_admin_status);

        // 3. Verify Welfare HOD can approve the IT requisition as acting Stores Head (second-tier)
        $responseApproveIT = $this->actingAs($welfareHOD)->post(route('main-admin.requisitions.process', $itReq->id), [
            'status' => 'approved',
        ]);
        $responseApproveIT->assertStatus(200);

        $itReq->refresh();
        $this->assertEquals('approved', $itReq->main_admin_status);
    }

    public function test_head_of_stores_requisitions_tracking_stepper(): void
    {
        // Hot-patch database columns in sqlite memory for testing
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}
        try {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        } catch (\Exception $e) {}

        // 1. Create a Head of Stores
        $headOfStores = User::factory()->create([
            'role' => 'Head of Stores',
            'department' => '',
            'is_admin' => true,
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 2. Create another admin/officer (not Head of Stores)
        $storeOfficer = User::factory()->create([
            'role' => 'Officer',
            'department' => 'Stores',
            'is_admin' => true,
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 3. Create a requisition
        $requisition = \App\Models\StoreRequisition::create([
            'requester_name' => 'IT Staff',
            'department' => 'IT Department',
            'purpose' => 'For testing tracking stepper',
            'priority' => 'normal',
            'status' => 'pending',
            'origin_admin_status' => 'pending',
            'main_admin_status' => 'approved',
            'usage_type' => 'permanent',
        ]);

        $item = \App\Models\StoreRequisitionItem::create([
            'requisition_id' => $requisition->id,
            'description' => 'Notebook',
            'category' => 'A',
            'unit' => 'Piece',
            'quantity_requested' => 10,
        ]);

        // A. Verify Head of Stores sees the tracking information on /admin/requisitions
        $response1 = $this->actingAs($headOfStores)->get('/admin/requisitions');
        $response1->assertStatus(200);
        $response1->assertSee('Next:', false);

        // B. Verify standard Store Officer (who is not Head of Stores) does NOT see the tracking information
        $response2 = $this->actingAs($storeOfficer)->get('/admin/requisitions');
        $response2->assertStatus(200);
        $response2->assertDontSee('Next:', false);
    }
}

