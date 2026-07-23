<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DGRequisitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Hotpatch columns in SQLite memory using individual Schema::table closures to prevent SQLite lockouts
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

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'origin_approved_by')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_approved_by')->nullable();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'stores_approved_by')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('stores_approved_by')->nullable();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'requires_dg_approval')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->boolean('requires_dg_approval')->default(false);
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_status')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('dg_status')->nullable();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_approved_by')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('dg_approved_by')->nullable();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_approved_at')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->timestamp('dg_approved_at')->nullable();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_decline_reason')) {
            \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->text('dg_decline_reason')->nullable();
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

        // Initialize settings table
        Setting::updateOrCreate(
            ['key' => 'dg_approval_categories'],
            ['value' => json_encode([]), 'type' => 'json', 'group' => 'inventory']
        );
        Setting::updateOrCreate(
            ['key' => 'stores_dept_head_approval_categories'],
            ['value' => json_encode([]), 'type' => 'json', 'group' => 'inventory']
        );
    }

    protected function createStock(string $description, string $category, float $quantity = 100): void
    {
        $admin = User::first() ?? User::factory()->create(['is_admin' => true]);
        $batch = InventoryBatch::create([
            'ledge_category' => $category,
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => $description,
            'stock_balance' => $quantity,
            'unit' => 'Piece',
        ]);
    }

    /**
     * Test admin can update DG approval categories configuration setting.
     */
    public function test_admin_can_update_dg_approval_setting(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'Head of Stores',
            'registration_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post('/admin/settings', [
            'settings_form' => '1',
            'dg_approval_categories' => ['J', 'K'],
        ]);

        $response->assertRedirect();
        
        $this->assertEquals(['J', 'K'], Setting::get('dg_approval_categories', []));
    }

    /**
     * Test that requisition with matching category requires DG approval.
     */
    public function test_requisition_with_matching_category_requires_dg_approval(): void
    {
        // 1. Configure J as a DG approval category
        Setting::updateOrCreate(
            ['key' => 'dg_approval_categories'],
            ['value' => json_encode(['J'])]
        );

        $user = User::factory()->create([
            'name' => 'Test Requester',
            'username' => 'requester1',
            'phone' => '0241112222',
            'role' => 'Staff',
            'service_number' => 'SRV123',
            'registration_status' => 'approved',
        ]);

        // Create stock
        $this->createStock('Heavy Drone J10', 'J', 10);

        // 2. Submit requisition containing item with category J
        $response = $this->actingAs($user)->postJson('/requisitions', [
            'requester_name' => 'Test Requester',
            'department' => 'Intelligence',
            'rank_or_title' => 'Sergeant',
            'purpose' => 'For surveillance tasks',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Heavy Drone J10',
                    'category' => 'J',
                    'unit' => 'Piece',
                    'quantity_requested' => 1,
                    'remarks' => '',
                ]
            ]
        ]);

        $response->assertStatus(200);

        $requisition = StoreRequisition::first();
        $this->assertNotNull($requisition);
        $this->assertTrue((bool)$requisition->requires_dg_approval);
        $this->assertEquals('pending', $requisition->dg_status);
    }

    /**
     * Test that requisition without matching category does not require DG approval.
     */
    public function test_requisition_without_matching_category_does_not_require_dg_approval(): void
    {
        // 1. Configure J as a DG approval category
        Setting::updateOrCreate(
            ['key' => 'dg_approval_categories'],
            ['value' => json_encode(['J'])]
        );

        $user = User::factory()->create([
            'name' => 'Test Requester',
            'username' => 'requester1',
            'phone' => '0241112222',
            'role' => 'Staff',
            'service_number' => 'SRV123',
            'registration_status' => 'approved',
        ]);

        // Create stock
        $this->createStock('Office Pen', 'A', 50);

        // 2. Submit requisition containing item with category A (not J)
        $response = $this->actingAs($user)->postJson('/requisitions', [
            'requester_name' => 'Test Requester',
            'department' => 'Intelligence',
            'rank_or_title' => 'Sergeant',
            'purpose' => 'For surveillance tasks',
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

        $requisition = StoreRequisition::first();
        $this->assertNotNull($requisition);
        $this->assertFalse((bool)$requisition->requires_dg_approval);
        $this->assertNull($requisition->dg_status);
    }

    /**
     * Test that store officer cannot checkout a requisition without DG approval when required.
     */
    public function test_store_officer_cannot_checkout_requisition_without_dg_approval(): void
    {
        $officer = User::factory()->create([
            'is_admin' => true,
            'role' => 'Head of Stores',
            'registration_status' => 'approved',
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => 'Test Staff',
            'department' => 'Stores',
            'purpose' => 'Test',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'approved',
            'requires_dg_approval' => true,
            'dg_status' => 'pending',
        ]);
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        $item = StoreRequisitionItem::create([
            'requisition_id' => $requisition->id,
            'description' => 'Test Item',
            'category' => 'J',
            'unit' => 'Piece',
            'quantity_requested' => 10,
        ]);

        // Create mock stock balance so it passes stock checks
        $this->createStock('Test Item', 'J', 50);

        // Attempt final store checkout approval as Officer
        $response = $this->actingAs($officer)->post("/admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
            'items' => [
                [
                    'id' => $item->id,
                    'quantity_approved' => 10,
                ]
            ]
        ]);

        // It should return a JSON error/exception (400 Bad Request) because it is caught in the Controller
        $response->assertStatus(400);
        $this->assertStringContainsString('requires Director General', $response->json('message'));
        
        $requisition->refresh();
        $this->assertEquals('pending', $requisition->status);
    }

    /**
     * Test Director General can approve a requisition.
     */
    public function test_dg_can_approve_requisition(): void
    {
        $dg = User::factory()->create([
            'role' => 'Director General',
            'registration_status' => 'approved',
        ]);

        // Create Admin to receive DG approval notification
        User::factory()->create([
            'is_admin' => true,
            'role' => 'Head of Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // Create Department Head to receive DG approval notification
        User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Intelligence',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => 'Test Staff',
            'department' => 'Intelligence',
            'purpose' => 'Test Purpose',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'approved',
            'requires_dg_approval' => true,
            'dg_status' => 'pending',
        ]);
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        $response = $this->actingAs($dg)->postJson("/dg/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->dg_status);
        $this->assertEquals($dg->name, $requisition->dg_approved_by);
        $this->assertNotNull($requisition->dg_approved_at);

        // Check automated notifications (Admin/HOD notifications)
        $this->assertGreaterThan(0, Message::count());
    }

    /**
     * Test Director General can decline a requisition.
     */
    public function test_dg_can_decline_requisition(): void
    {
        $dg = User::factory()->create([
            'role' => 'Director General',
            'registration_status' => 'approved',
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => 'Test Staff',
            'department' => 'Intelligence',
            'purpose' => 'Test Purpose',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'approved',
            'requires_dg_approval' => true,
            'dg_status' => 'pending',
        ]);
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        $response = $this->actingAs($dg)->postJson("/dg/requisitions/{$requisition->id}/process", [
            'status' => 'declined',
            'decline_reason' => 'Invalid justification',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        $this->assertEquals('declined', $requisition->dg_status);
        $this->assertEquals('declined', $requisition->status); // Requisition overall status is declined
        $this->assertEquals('Invalid justification', $requisition->dg_decline_reason);
    }

    /**
     * Test that a Delegator (Sub Main Admin) can approve their own department's pending requisition.
     */
    public function test_delegator_can_approve_own_department_requisition(): void
    {
        $delegator = User::factory()->create([
            'role' => 'Sub Main Admin',
            'department' => 'HR',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => 'HR Staff',
            'department' => 'HR',
            'purpose' => 'Need stationaries',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
        ]);

        $response = $this->actingAs($delegator)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        // Delegator is a Sub Main Admin acting as HOD for their department:
        // approval must cover BOTH origin_admin_status AND main_admin_status (dual-role)
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('approved', $requisition->main_admin_status,
            'Sub Main Admin (Delegator) should simultaneously approve both HOD and Authorizer steps.');
    }

    /**
     * Test that a Delegator (Sub Main Admin) dual-role approval notifies Head of Stores directly.
     */
    public function test_delegator_dual_role_approval_notifies_head_of_stores(): void
    {
        $delegator = User::factory()->create([
            'role'                => 'Sub Main Admin',
            'department'          => 'Logistics',
            'registration_status' => 'approved',
            'is_active'           => true,
        ]);

        // Head of Stores that should receive the notification
        $headOfStores = User::factory()->create([
            'is_admin'            => true,
            'role'                => 'Head of Stores',
            'registration_status' => 'approved',
            'is_active'           => true,
        ]);

        $requisition = StoreRequisition::create([
            'requester_name'      => 'Logistics Staff',
            'department'          => 'Logistics',
            'purpose'             => 'Procurement of fuel',
            'priority'            => 'urgent',
            'status'              => 'pending',
            'usage_type'          => 'permanent',
            'origin_admin_status' => 'pending',
            'requires_dg_approval'=> false,
        ]);

        $response = $this->actingAs($delegator)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        // Both steps should be approved in a single action
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('approved', $requisition->main_admin_status);
        $this->assertEquals($delegator->name, $requisition->origin_approved_by);
        $this->assertEquals($delegator->name, $requisition->stores_approved_by);

        // Head of Stores should have received an automated notification
        $this->assertGreaterThan(0, Message::where('receiver_id', $headOfStores->id)->count(),
            'Head of Stores should be notified after delegator dual-role approval.');
    }

    /**
     * Test that a Delegator (Sub Main Admin) can decline their own department's pending requisition.
     */
    public function test_delegator_can_decline_own_department_requisition(): void
    {
        $delegator = User::factory()->create([
            'role' => 'Sub Main Admin',
            'department' => 'HR',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => 'HR Staff',
            'department' => 'HR',
            'purpose' => 'Need stationaries',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
        ]);

        $response = $this->actingAs($delegator)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'declined',
            'decline_reason' => 'Not needed',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $requisition->refresh();
        $this->assertEquals('declined', $requisition->origin_admin_status);
        $this->assertEquals('declined', $requisition->status);
        $this->assertEquals('Not needed', $requisition->decline_reason);
    }

    /**
     * Test that when a Sub Main Admin submits their own department's requisition,
     * both origin_admin_status and main_admin_status are auto-approved at creation (dual-role bypass).
     */
    public function test_sub_main_admin_self_submission_skips_authorizer_review(): void
    {
        $delegator = User::factory()->create([
            'name'                => 'Auth Officer',
            'username'            => 'authofficer',
            'phone'               => '0241234567',
            'role'                => 'Sub Main Admin',
            'department'          => 'Finance',
            'service_number'      => 'FIN001',
            'registration_status' => 'approved',
            'is_active'           => true,
        ]);

        // Head of Stores to receive notification
        User::factory()->create([
            'is_admin'            => true,
            'role'                => 'Head of Stores',
            'registration_status' => 'approved',
            'is_active'           => true,
        ]);

        $this->createStock('Paper Ream', 'A', 100);

        $response = $this->actingAs($delegator)->postJson('/requisitions', [
            'requester_name' => 'Auth Officer',
            'department'     => 'Finance',
            'rank_or_title'  => 'Captain',
            'purpose'        => 'Office supplies for quarter',
            'priority'       => 'normal',
            'usage_type'     => 'permanent',
            'items'          => [
                [
                    'description'        => 'Paper Ream',
                    'category'           => 'A',
                    'unit'               => 'Piece',
                    'quantity_requested' => 5,
                    'remarks'            => '',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $requisition = StoreRequisition::latest()->first();
        $this->assertNotNull($requisition);

        // Both HOD and Authorizer steps should be auto-approved at submission
        $this->assertEquals('approved', $requisition->origin_admin_status,
            'origin_admin_status should be auto-approved when Sub Main Admin submits own dept request.');
        $this->assertEquals('approved', $requisition->main_admin_status,
            'main_admin_status should be auto-approved (Authorizer review skipped) when Sub Main Admin submits own dept request.');

        // DG approval must be bypassed — straight to Head of Stores
        $this->assertFalse((bool) $requisition->requires_dg_approval,
            'DG approval should be bypassed for Sub Main Admin self-submissions.');
        $this->assertNull($requisition->dg_status,
            'dg_status should be null (DG bypassed) for Sub Main Admin self-submissions.');

        // Status badge must be exactly "Awaiting Head of Stores Review"
        $badge = $requisition->status_badge;
        $this->assertEquals('Awaiting Head of Stores Review', $badge['label'],
            'Status should be Awaiting Head of Stores Review immediately after Sub Main Admin self-submission.');
    }

    /**
     * Test that sidebar badge count disappears (becomes 0) when a request is awaiting other heads.
     */
    public function test_sidebar_badge_count_disappears_when_awaiting_other_heads(): void
    {
        $hrHOD = User::factory()->create([
            'name' => 'HR HOD',
            'role' => 'Department Head',
            'department' => 'HR',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $headOfStores = User::factory()->create([
            'name' => 'Head of Stores',
            'is_admin' => true,
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $hrStaff = User::factory()->create([
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 1. Requisition submitted by HR staff -> awaiting HR HOD (origin_admin_status = pending)
        $requisition = StoreRequisition::create([
            'requester_name' => $hrStaff->name,
            'department' => 'HR',
            'requested_by' => $hrStaff->id,
            'purpose' => 'HR Stationeries',
            'priority' => 'normal',
            'status' => 'pending',
            'origin_admin_status' => 'pending',
            'main_admin_status' => 'pending',
            'usage_type' => 'permanent',
        ]);

        // For Head of Stores, request is awaiting HR HOD (other head), so badge is hidden (display: none)
        $viewStores = $this->actingAs($headOfStores)->get(route('main-admin.requisitions'));
        $viewStores->assertStatus(200);
        $contentStores = $viewStores->getContent();
        $this->assertStringContainsString('id="sidebar-badge-main-reqs"', $contentStores);
        $this->assertStringContainsString('display: none', $contentStores);

        // For HR HOD, request is awaiting HR HOD, so badge is visible (display: flex) with count 1
        $viewHOD = $this->actingAs($hrHOD)->get(route('main-admin.requisitions'));
        $viewHOD->assertStatus(200);
        $contentHOD = $viewHOD->getContent();
        $this->assertStringContainsString('id="sidebar-badge-main-reqs"', $contentHOD);
        $this->assertStringContainsString('display: flex', $contentHOD);

        // 2. HR HOD approves the requisition
        $response = $this->actingAs($hrHOD)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ]);
        $response->assertStatus(200);

        // After HR HOD approves:
        // For HR HOD, request is now awaiting Stores Head (other head), so badge is hidden (display: none)
        $viewHOD2 = $this->actingAs($hrHOD)->get(route('main-admin.requisitions'));
        $viewHOD2->assertStatus(200);
        $contentHOD2 = $viewHOD2->getContent();
        $this->assertStringContainsString('id="sidebar-badge-main-reqs"', $contentHOD2);
        $this->assertStringContainsString('display: none', $contentHOD2);

        // For Stores Head, request is now ready for Stores Head action, so badge is visible (display: flex)
        $viewStores2 = $this->actingAs($headOfStores)->get(route('main-admin.requisitions'));
        $viewStores2->assertStatus(200);
        $contentStores2 = $viewStores2->getContent();
        $this->assertStringContainsString('id="sidebar-badge-main-reqs"', $contentStores2);
        $this->assertStringContainsString('display: flex', $contentStores2);
    }

    /**
     * Test that when Head of HR (Sub Main Admin) makes a requisition request
     * for an item category requiring DG approval, it requires DG approval.
     */
    public function test_head_of_hr_self_submission_for_dg_category_requires_dg_approval(): void
    {
        \App\Models\Setting::set('dg_approval_categories', ['B']);

        $headOfHr = User::factory()->create([
            'name'                => 'Head of HR',
            'username'            => 'headofhr',
            'phone'               => '0241234567',
            'service_number'      => 'HR001',
            'role'                => 'Sub Main Admin',
            'department'          => 'Human Resource Management Department',
            'registration_status' => 'approved',
            'is_active'           => true,
        ]);

        $this->createStock('Patrol Armored SUV', 'B', 10);

        $response = $this->actingAs($headOfHr)->postJson('/requisitions', [
            'requester_name' => 'Head of HR',
            'department'     => 'Human Resource Management Department',
            'rank_or_title'  => 'HR Director',
            'purpose'        => 'Staff Transport Operations',
            'priority'       => 'urgent',
            'usage_type'     => 'permanent',
            'items'          => [
                [
                    'description'        => 'Patrol Armored SUV',
                    'category'           => 'B',
                    'unit'               => 'Vehicle',
                    'quantity_requested' => 1,
                    'remarks'            => 'Requires DG clearance',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $requisition = StoreRequisition::latest()->first();
        $this->assertNotNull($requisition);

        // HOD and Authorizer steps should be auto-approved, but DG approval MUST be required
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('approved', $requisition->main_admin_status);
        $this->assertTrue((bool)$requisition->requires_dg_approval, 'Head of HR request for DG category must require DG approval.');
        $this->assertEquals('pending', $requisition->dg_status);
        $this->assertTrue((bool)$requisition->is_ready_for_dg_approval, 'Requisition must be ready for DG approval.');
    }
}
