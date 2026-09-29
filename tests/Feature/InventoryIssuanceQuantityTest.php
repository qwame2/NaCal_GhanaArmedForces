<?php

namespace Tests\Feature;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Setting;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryIssuanceQuantityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $requisitioner;
    private User $deptHead;

    protected function setUp(): void
    {
        parent::setUp();

        // Hotpatch SQLite schema columns to ensure clean in-memory execution if needed
        if (!Schema::hasColumn('store_requisitions', 'main_admin_status')) {
            Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('main_admin_status')->default('pending');
            });
        }
        if (!Schema::hasColumn('store_requisitions', 'origin_admin_status')) {
            Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('origin_admin_status')->default('pending');
            });
        }
        if (!Schema::hasColumn('store_requisitions', 'alternative_status')) {
            Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('alternative_status')->nullable();
            });
        }
        if (!Schema::hasColumn('store_requisitions', 'requires_dg_approval')) {
            Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->boolean('requires_dg_approval')->default(false);
            });
        }
        if (!Schema::hasColumn('store_requisitions', 'dg_status')) {
            Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('dg_status')->nullable();
            });
        }

        Setting::updateOrCreate(
            ['key' => 'dg_approval_categories'],
            ['value' => json_encode([]), 'type' => 'json', 'group' => 'inventory']
        );

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'Head of Stores',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $this->requisitioner = User::factory()->create([
            'role' => 'Requisitioner',
            'department' => 'Logistics Department',
            'phone' => '0241000001',
            'service_number' => 'SN001',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);

        $this->deptHead = User::factory()->create([
            'role' => 'Department Head',
            'department' => 'Logistics Department',
            'registration_status' => 'approved',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to approve and issue a requisition through the store process flow.
     */
    private function submitAndIssueRequisition(string $itemDescription, string $category, float $qtyToIssue): void
    {
        // 1. Submit requisition
        $respSubmit = $this->actingAs($this->requisitioner)->postJson('/requisitions', [
            'requester_name' => $this->requisitioner->name,
            'department' => 'Logistics Department',
            'rank_or_title' => 'Officer',
            'purpose' => 'Issuance test for ' . $itemDescription,
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => $itemDescription,
                    'category' => $category,
                    'unit' => 'Pieces',
                    'quantity_requested' => $qtyToIssue,
                    'remarks' => 'Issuance test',
                ]
            ]
        ]);
        $respSubmit->assertStatus(200);

        $requisition = StoreRequisition::where('purpose', 'Issuance test for ' . $itemDescription)
            ->latest('id')
            ->first();
        $this->assertNotNull($requisition);

        // 2. HOD Approves
        $this->actingAs($this->deptHead)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ])->assertStatus(200);

        $requisition->refresh();
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        // 3. Head of Stores Approves & Issues
        $reqItem = $requisition->items->first();
        $this->actingAs($this->admin)->post("/admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
            'items' => [
                [
                    'id' => $reqItem->id,
                    'quantity_approved' => $qtyToIssue,
                    'remarks' => 'Approved and issued in full',
                ]
            ]
        ])->assertStatus(200);
    }

    /**
     * Example 1:
     * Received Qty: 8, Issued Qty: 0, Stock Balance: 8
     * Then issue 4:
     * Received Qty: 8 (unchanged)
     * Issued Qty: 4
     * Stock Balance: 4
     */
    public function test_example_1_issuing_an_item_only_decreases_stock_balance(): void
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-EX1-001',
            'supplier_name' => 'Gov Supplies',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $this->admin->id,
            'ledge_category' => 'STATIONERY',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'PILLOW SHEETS',
            'received_qty' => 8,
            'qty' => 8,
            'stock_balance' => 8,
            'unit' => 'Pieces',
            'unit_cost' => 50,
            'variance' => 0,
        ]);

        $this->assertEquals(8, (float) $item->received_qty);
        $this->assertEquals(8, (float) $item->qty);
        $this->assertEquals(8, (float) $item->stock_balance);

        // Issue 4
        $this->submitAndIssueRequisition('PILLOW SHEETS', 'STATIONERY', 4);

        $item->refresh();

        // Verify: stock_balance is reduced to 4, but received_qty and qty remain 8
        $this->assertEquals(4, (float) $item->stock_balance, 'Stock balance should be reduced from 8 to 4');
        $this->assertEquals(8, (float) $item->received_qty, 'received_qty should remain unchanged at 8');
        $this->assertEquals(8, (float) $item->qty, 'qty column should remain unchanged at 8');
        $this->assertEquals(8, (float) $item->original_received_qty, 'original_received_qty accessor should remain 8');
    }

    /**
     * Example 2:
     * Received Qty: 1, Issued Qty: 0, Stock Balance: 1
     * Then issue 1:
     * Received Qty: 1 (unchanged)
     * Issued Qty: 1
     * Stock Balance: 0
     */
    public function test_example_2_issuing_full_stock_leaves_received_qty_intact(): void
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-EX2-001',
            'supplier_name' => 'Office Direct',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $this->admin->id,
            'ledge_category' => 'IT',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'TONER 59A',
            'received_qty' => 1,
            'qty' => 1,
            'stock_balance' => 1,
            'unit' => 'Pieces',
            'unit_cost' => 120,
            'variance' => 0,
        ]);

        $this->assertEquals(1, (float) $item->received_qty);
        $this->assertEquals(1, (float) $item->stock_balance);

        // Issue 1
        $this->submitAndIssueRequisition('TONER 59A', 'IT', 1);

        $item->refresh();

        // Verify: stock_balance is 0, but received_qty remains 1
        $this->assertEquals(0, (float) $item->stock_balance, 'Stock balance should be 0');
        $this->assertEquals(1, (float) $item->received_qty, 'received_qty should remain 1');
        $this->assertEquals(1, (float) $item->qty, 'qty column should remain 1');
    }

    /**
     * Test multiple issuances accumulate correctly:
     * Stock Balance = Received Quantity − Total Issued Quantity
     */
    public function test_multiple_issuances_accumulate_correctly(): void
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-MULTI-001',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $this->admin->id,
            'ledge_category' => 'GENERAL',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 PAPER BOX',
            'received_qty' => 10,
            'qty' => 10,
            'stock_balance' => 10,
            'unit' => 'Boxes',
            'unit_cost' => 25,
            'variance' => 0,
        ]);

        // First issuance: 3 units
        $this->submitAndIssueRequisition('A4 PAPER BOX', 'GENERAL', 3);
        $item->refresh();
        $this->assertEquals(7, (float) $item->stock_balance);
        $this->assertEquals(10, (float) $item->received_qty);

        // Second issuance: 4 units
        $this->submitAndIssueRequisition('A4 PAPER BOX', 'GENERAL', 4);
        $item->refresh();
        $this->assertEquals(3, (float) $item->stock_balance);
        $this->assertEquals(10, (float) $item->received_qty);

        // Third issuance: 3 units (exhausting stock)
        $this->submitAndIssueRequisition('A4 PAPER BOX', 'GENERAL', 3);
        $item->refresh();
        $this->assertEquals(0, (float) $item->stock_balance);
        $this->assertEquals(10, (float) $item->received_qty);
        $this->assertEquals(10, (float) $item->qty);
    }

    /**
     * Test editing an item's received quantity recalculates stock_balance using:
     * stock_balance = new_received_qty - total_issued_quantity
     */
    public function test_editing_received_quantity_preserves_past_issuances(): void
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-EDIT-001',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $this->admin->id,
            'ledge_category' => 'TOOLS',
            'acquisition_type' => 'Standard',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'HEAVY HAMMERS',
            'received_qty' => 10,
            'qty' => 10,
            'stock_balance' => 10,
            'unit' => 'Pieces',
            'unit_cost' => 30,
            'variance' => 0,
        ]);

        // Issue 4 units -> stock_balance is now 6 (total issued = 4)
        $this->submitAndIssueRequisition('HEAVY HAMMERS', 'TOOLS', 4);
        $item->refresh();
        $this->assertEquals(6, (float) $item->stock_balance);
        $this->assertEquals(10, (float) $item->received_qty);

        // Now edit the batch item via ReceivedItemsController@update
        // Correcting received quantity from 10 to 12
        $response = $this->actingAs($this->admin)->putJson("/received-items/{$batch->id}", [
            'entry_date' => $batch->entry_date,
            'arrival_date' => now()->toDateString(),
            'acquisition_type' => 'Standard',
            'ledge_category' => 'TOOLS',
            'supplier_status' => 'Approved',
            'supplier_name' => 'Supplier Co',
            'items' => [
                [
                    'id' => $item->id,
                    'description' => 'HEAVY HAMMERS',
                    'unit' => 'Pieces',
                    'qty' => 12, // New received qty: 12
                    'stock_balance' => 8, // Frontend calculated: 12 - 4 = 8
                    'unit_cost' => 30,
                    'variance' => 0,
                    'store_location' => 'Store A',
                ]
            ]
        ]);

        $this->assertTrue($response->isSuccessful(), $response->json('message') ?? $response->getContent());

        $item->refresh();
        // With new received_qty 12 and 4 issued:
        // stock_balance = 12 - 4 = 8
        $this->assertEquals(12, (float) $item->received_qty, 'received_qty should be updated to 12');
        $this->assertEquals(12, (float) $item->qty, 'qty should be updated to 12');
        $this->assertEquals(8, (float) $item->stock_balance, 'stock_balance should be 12 - 4 = 8');
    }

    /**
     * Test alternative item FIFO issuance only deducts stock_balance of the alternative item,
     * leaving its received_qty and qty unchanged.
     */
    public function test_alternative_item_issuance_only_deducts_stock_balance(): void
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-ALT-001',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Approved',
            'approval_status' => 'approved',
            'entry_date' => now()->toDateString(),
            'recorded_by' => $this->admin->id,
            'ledge_category' => 'STATIONERY',
        ]);

        $origItem = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'BLUE PENS',
            'received_qty' => 10,
            'qty' => 10,
            'stock_balance' => 10,
            'unit' => 'Pieces',
            'unit_cost' => 5,
            'variance' => 0,
        ]);

        $altItem = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'BLUE BALLPOINT PENS',
            'received_qty' => 50,
            'qty' => 50,
            'stock_balance' => 50,
            'unit' => 'Pieces',
            'unit_cost' => 5,
            'variance' => 0,
        ]);

        // Submit requisition for standard 'BLUE PENS'
        $respSubmit = $this->actingAs($this->requisitioner)->postJson('/requisitions', [
            'requester_name' => $this->requisitioner->name,
            'department' => 'Logistics Department',
            'rank_or_title' => 'Officer',
            'purpose' => 'Alternative item issuance test',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'BLUE PENS',
                    'category' => 'STATIONERY',
                    'unit' => 'Pieces',
                    'quantity_requested' => 10,
                    'remarks' => 'Blue pens needed',
                ]
            ]
        ]);
        $respSubmit->assertStatus(200);

        $requisition = StoreRequisition::where('purpose', 'Alternative item issuance test')->first();

        // HOD approves
        $this->actingAs($this->deptHead)->postJson("/main-admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
        ])->assertStatus(200);

        $requisition->refresh();
        $requisition->main_admin_status = 'approved';
        $requisition->save();

        // Stores head approves using alternative item 'BLUE BALLPOINT PENS' with qty 10
        $reqItem = $requisition->items->first();
        $responseAdminProcess = $this->actingAs($this->admin)->post("/admin/requisitions/{$requisition->id}/process", [
            'status' => 'approved',
            'items' => [
                [
                    'id' => $reqItem->id,
                    'quantity_approved' => 0,
                    'alternative_description' => 'BLUE BALLPOINT PENS',
                    'alternative_quantity_approved' => 10,
                    'remarks' => 'Supplied alternative blue ballpoint pens',
                ]
            ]
        ]);
        $responseAdminProcess->assertStatus(200);

        $altItem->refresh();
        // Alt item stock_balance should be reduced from 50 to 40
        $this->assertEquals(40, (float) $altItem->stock_balance, 'Alternative item stock balance should decrease to 40');
        // received_qty and qty should remain unchanged at 50
        $this->assertEquals(50, (float) $altItem->received_qty, 'Alternative item received_qty should remain 50');
        $this->assertEquals(50, (float) $altItem->qty, 'Alternative item qty should remain 50');
    }
}
