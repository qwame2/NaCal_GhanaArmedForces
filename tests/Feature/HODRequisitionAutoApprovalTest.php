<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Message;
use App\Models\SystemLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HODRequisitionAutoApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Hotpatch columns in SQLite memory
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'hod_auto_approve_timeout_mins')) {
            \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->unsignedInteger('hod_auto_approve_timeout_mins')->default(5);
            });
        }

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

    public function test_requisition_created_less_than_5_minutes_ago_is_not_auto_approved()
    {
        $user = User::factory()->create([
            'name' => 'HR Staff',
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need stationaries',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
        ]);
        $requisition->created_at = now()->subMinutes(4); // 4 minutes ago (not yet 5 minutes)
        $requisition->save();

        StoreRequisition::autoApproveOverdueHODRequisitions();

        $requisition->refresh();
        $this->assertEquals('pending', $requisition->origin_admin_status);
    }

    public function test_requisition_created_more_than_5_minutes_ago_is_auto_approved_by_system()
    {
        // Create a Director General user to receive HOD approval notifications (if required)
        $dg = User::factory()->create([
            'role' => 'Director General',
            'is_active' => true,
        ]);

        // Create Stores Department Head to receive notifications
        $storesHead = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Stores',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'HR Staff',
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
        ]);

        // Scenario A: Without DG approval required
        $requisition = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need stationaries',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
            'requires_dg_approval' => false,
        ]);
        $requisition->created_at = now()->subMinutes(6); // 6 minutes ago (exceeded 5 mins)
        $requisition->save();

        StoreRequisition::autoApproveOverdueHODRequisitions();

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('System Auto-Approved', $requisition->origin_approved_by);

        // Verify System Log
        $this->assertTrue(SystemLog::where('action', 'AUTO_APPROVE_HOD')
            ->where('metadata->requisition_id', $requisition->id)
            ->exists());

        // Verify Message Notification sent to Stores Head
        $this->assertTrue(Message::where('receiver_id', $storesHead->id)
            ->where('is_automated', true)
            ->where('message', 'like', '%AUTO-APPROVED BY DEPT%')
            ->exists());
    }

    public function test_requisition_requiring_dg_approval_escalates_to_dg_upon_auto_approval()
    {
        $dg = User::factory()->create([
            'role' => 'Director General',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'HR Staff',
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
        ]);

        // Scenario B: With DG approval required
        $requisition = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need drones',
            'priority' => 'urgent',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
            'requires_dg_approval' => true,
            'dg_status' => 'pending',
        ]);
        $requisition->created_at = now()->subMinutes(6); // 6 minutes ago
        $requisition->save();

        StoreRequisition::autoApproveOverdueHODRequisitions();

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('System Auto-Approved', $requisition->origin_approved_by);

        // Verify Message Notification sent to DG
        $this->assertTrue(Message::where('receiver_id', $dg->id)
            ->where('is_automated', true)
            ->where('message', 'like', '%NEW REQUISITION AWAITING DG APPROVAL%')
            ->exists());
    }

    public function test_lazy_checking_triggers_auto_approval_on_page_load()
    {
        $storesHead = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Stores',
            'is_active' => true,
            'registration_status' => 'approved',
        ]);

        $user = User::factory()->create([
            'name' => 'HR Staff',
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need items',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
            'requires_dg_approval' => false,
        ]);
        $requisition->created_at = now()->subMinutes(6); // 6 minutes ago
        $requisition->save();

        // Simulating visiting HOD/Stores index page which lazy-checks temporary items and overdue HOD approvals
        $this->actingAs($storesHead)->get(route('main-admin.requisitions'));

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->origin_admin_status);
        $this->assertEquals('System Auto-Approved', $requisition->origin_approved_by);
    }



    public function test_requisition_respects_global_auto_approval_timeout_setting()
    {
        // 1. Set the global default timeout setting to 15 minutes
        Setting::updateOrCreate(
            ['key' => 'default_hod_auto_approve_timeout_mins'],
            ['value' => '15', 'type' => 'integer', 'group' => 'general']
        );

        $user = User::factory()->create([
            'name' => 'HR Staff',
            'role' => 'Requisitioner',
            'department' => 'HR',
            'registration_status' => 'approved',
        ]);

        // Scenario A: Created 12 minutes ago (not yet global 15 minutes limit)
        $requisitionA = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need paper',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
        ]);
        $requisitionA->created_at = now()->subMinutes(12);
        $requisitionA->save();

        // Scenario B: Created 18 minutes ago (exceeded global 15 minutes limit)
        $requisitionB = StoreRequisition::create([
            'requester_name' => $user->name,
            'department' => 'HR',
            'requested_by' => $user->id,
            'purpose' => 'Need toner',
            'priority' => 'normal',
            'status' => 'pending',
            'usage_type' => 'permanent',
            'origin_admin_status' => 'pending',
        ]);
        $requisitionB->created_at = now()->subMinutes(18);
        $requisitionB->save();

        StoreRequisition::autoApproveOverdueHODRequisitions();

        $requisitionA->refresh();
        $requisitionB->refresh();

        // Requisition A should still be pending HOD approval
        $this->assertEquals('pending', $requisitionA->origin_admin_status);

        // Requisition B should be auto-approved
        $this->assertEquals('approved', $requisitionB->origin_admin_status);
        $this->assertEquals('System Auto-Approved', $requisitionB->origin_approved_by);

        // Verify log entry contains the global setting timeout value
        $this->assertTrue(SystemLog::where('action', 'AUTO_APPROVE_HOD')
            ->where('metadata->requisition_id', $requisitionB->id)
            ->where('description', 'like', '%timeout: 15 minutes%')
            ->exists());
    }
}
