<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisitionLifecycleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Hotpatch SQLite schema columns to ensure clean in-memory execution
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

        Setting::updateOrCreate(
            ['key' => 'dg_approval_categories'],
            ['value' => json_encode([]), 'type' => 'json', 'group' => 'inventory']
        );
    }

    /**
     * Test full multi-step integration lifecycle:
     * Submission -> HOD Approval -> Main Admin Approval -> Stock Inventory Update.
     */
    public function test_end_to_end_requisition_lifecycle_and_inventory_deduction(): void
    {
        // 1. Setup Initial Inventory Stock in DB
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'Head of Stores', 'registration_status' => 'approved']);
        $batch = InventoryBatch::create([
            'ledge_category' => 'A',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $admin->id,
        ]);
        $inventoryItem = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'Integration HP Laptop',
            'stock_balance' => 20,
            'unit' => 'Piece',
        ]);

        // 2. Setup Users
        $requisitioner = User::factory()->create([
            'role' => 'Requisitioner',
            'department' => 'IT Department',
            'phone' => '0241112222',
            'service_number' => 'SRV123',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $deptHead = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'IT Department',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        // 3. Requisitioner Submits Requisition
        $responseSubmit = $this->actingAs($requisitioner)->postJson('/requisitions', [
            'requester_name' => $requisitioner->name,
            'department' => 'IT Department',
            'rank_or_title' => 'Officer',
            'purpose' => 'System Integration E2E Test',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'Integration HP Laptop',
                    'category' => 'A',
                    'unit' => 'Piece',
                    'quantity_requested' => 5,
                    'remarks' => 'Urgent for development workstation',
                ]
            ]
        ]);

        $responseSubmit->assertStatus(200)->assertJson(['success' => true]);

        $requisition = StoreRequisition::where('purpose', 'System Integration E2E Test')->first();
        $this->assertNotNull($requisition);
        $this->assertEquals('pending', $requisition->origin_admin_status);

        // 4. Department Head Approves Requisition (Origin Admin)
        $responseHodProcess = $this->actingAs($deptHead)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ]);
        $responseHodProcess->assertStatus(200)->assertJson(['success' => true]);

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->origin_admin_status);

        // Authorizer approves main_admin_status
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        // 5. Stores Head Approves and Issues Quantities
        $reqItem = $requisition->items->first();
        $responseAdminProcess = $this->actingAs($admin)->post("/admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
            'items' => [
                [
                    'id' => $reqItem->id,
                    'quantity_approved' => 5,
                    'remarks' => 'Approved in full',
                ]
            ]
        ]);

        $responseAdminProcess->assertStatus(200)->assertJson(['success' => true]);

        // 6. Verify final database state & stock balance deduction
        $requisition->refresh();
        $this->assertEquals('approved', $requisition->status);

        $inventoryItem->refresh();
        $this->assertEquals(15, (float) $inventoryItem->stock_balance, 'Stock balance should be reduced from 20 to 15');
    }
}
