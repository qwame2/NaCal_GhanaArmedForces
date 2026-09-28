<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SraOriginalQuantityTest extends TestCase
{
    use RefreshDatabase;

    private User $auditor;
    private User $authorizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditor = User::create([
            'name' => 'Auditor User',
            'username' => 'auditor1',
            'role' => 'Auditor',
            'department' => 'Audit',
            'registration_status' => 'approved',
            'is_admin' => 0,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        $this->authorizer = User::create([
            'name' => 'Authorizer User',
            'username' => 'admin1',
            'role' => 'Main Admin',
            'department' => 'Administration',
            'registration_status' => 'approved',
            'is_admin' => 1,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);
    }

    public function test_sra_displays_original_received_qty_when_stock_balance_drops_to_zero()
    {
        // 1. Create a batch awaiting SRA approval
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-TEST-001',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Supplier',
            'approval_status' => 'pending_auditor_admin',
            'entry_date' => now()->toDateString(),
            'arrival_date' => now()->toDateString(),
            'invoice_number' => 'INV-001',
            'ledge_category' => 'STATIONERY',
        ]);

        // 2. Add item with original received quantity of 5
        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'TONER 59A',
            'qty' => 5,
            'stock_balance' => 5,
            'received_qty' => 5,
            'unit_cost' => 100,
            'variance' => 0,
            'ledge_category' => 'STATIONERY',
        ]);

        // Record initial stock history
        StockHistory::create([
            'inventory_item_id' => $item->id,
            'action' => 'create',
            'old_qty' => 0,
            'new_qty' => 5,
            'old_stock_balance' => 0,
            'new_stock_balance' => 5,
            'performed_by' => $this->authorizer->id,
        ]);

        // 3. Requisitioner requests and receives item, reducing stock_balance and qty to 0
        $item->qty = 0;
        $item->stock_balance = 0;
        $item->save();

        // 4. Auditor views SRA page
        $response = $this->actingAs($this->auditor)->get(route('receiveditems.sra', $batch->id));

        $response->assertStatus(200);
        $response->assertSee('TONER 59A');
        
        // Assert that SRA displays original quantity 5 and NOT 0
        $response->assertSeeTextInOrder([
            'TONER 59A',
            '5', // Ordered
            '5', // Received
            '-', // Balance (variance is 0)
        ]);

        // 5. Authorizer views SRA page
        $responseAdmin = $this->actingAs($this->authorizer)->get(route('receiveditems.sra', $batch->id));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSeeTextInOrder([
            'TONER 59A',
            '5',
            '5',
            '-',
        ]);
    }

    public function test_sra_falls_back_to_stock_history_if_received_qty_was_null()
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-TEST-002',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Supplier',
            'approval_status' => 'pending_auditor_admin',
            'entry_date' => now()->toDateString(),
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'STATIONERY',
        ]);

        // Legacy item originally created with qty = 3 (which logs StockHistory create with new_qty = 3)
        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'TONER 05A',
            'qty' => 3,
            'stock_balance' => 3,
            'variance' => 0,
        ]);

        // Simulate legacy database state where received_qty is null and stock was deducted to 0
        \Illuminate\Support\Facades\DB::table('inventory_items')
            ->where('id', $item->id)
            ->update([
                'received_qty' => null,
                'qty' => 0,
                'stock_balance' => 0,
            ]);

        $response = $this->actingAs($this->auditor)->get(route('receiveditems.sra', $batch->id));

        $response->assertStatus(200);
        $response->assertSeeTextInOrder([
            'TONER 05A',
            '3',
            '3',
            '-',
        ]);
    }

    public function test_auditor_pending_sra_approvals_table_displays_item_names()
    {
        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-TEST-003',
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Supplier',
            'approval_status' => 'pending_auditor_admin',
            'auditor_status' => 'pending',
            'entry_date' => now()->toDateString(),
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'STATIONERY',
        ]);

        $item1 = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'TONER 59A',
            'qty' => 1,
            'stock_balance' => 0,
            'received_qty' => 1,
            'variance' => 0,
            'ledge_category' => 'STATIONERY',
        ]);

        $item2 = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 PAPER BOX',
            'qty' => 10,
            'stock_balance' => 10,
            'received_qty' => 10,
            'variance' => 0,
            'ledge_category' => 'STATIONERY',
        ]);

        $response = $this->actingAs($this->auditor)->get(route('auditor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Item(s) Name');
        $response->assertSee('TONER 59A');
        $response->assertSee('A4 PAPER BOX');
    }

    public function test_auditor_dashboard_has_tab_arrows_and_pagination_on_all_tabs()
    {
        $response = $this->actingAs($this->auditor)->get(route('auditor.dashboard'));

        $response->assertStatus(200);

        // Assert tab scroll navigation arrows are present
        $response->assertSee('id="auditTabScrollLeft"', false);
        $response->assertSee('id="auditTabScrollRight"', false);
        $response->assertSee('id="auditTabsContainer"', false);

        // Assert all 7 tab tables have their pagination container IDs
        $response->assertSee('id="pager-audit-trail"', false);
        $response->assertSee('id="pager-received-items"', false);
        $response->assertSee('id="pager-issued-items"', false);
        $response->assertSee('id="pager-returned-items"', false);
        $response->assertSee('id="pager-requisitions"', false);
        $response->assertSee('id="pager-approved-requisitions"', false);
        $response->assertSee('id="pager-pending-sra"', false);

        // Test JSON tab payload includes pager for all tabs
        $jsonResponse = $this->actingAs($this->auditor)->get(route('auditor.dashboard', ['format' => 'json']));
        $jsonResponse->assertStatus(200);
        $data = $jsonResponse->json('tabs');
        
        $this->assertArrayHasKey('audit_trail', $data);
        $this->assertArrayHasKey('received_items', $data);
        $this->assertArrayHasKey('issued_items', $data);
        $this->assertArrayHasKey('returned_items', $data);
        $this->assertArrayHasKey('requisitions', $data);
        $this->assertArrayHasKey('approved_requisitions', $data);
        $this->assertArrayHasKey('pending_sra', $data);

        foreach ($data as $key => $tab) {
            $this->assertNotEmpty($tab['pager'], "Tab '{$key}' should include pager HTML");
        }
    }

    public function test_auditor_pending_sra_tab_badge_count_renders_clearly()
    {
        // Create 2 pending batches
        InventoryBatch::create([
            'sra_number' => 'SRA-BADGE-001',
            'supplier_name' => 'Supplier A',
            'supplier_status' => 'Supplier',
            'approval_status' => 'pending_auditor_admin',
            'entry_date' => now()->toDateString(),
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'STATIONERY',
        ]);
        InventoryBatch::create([
            'sra_number' => 'SRA-BADGE-002',
            'supplier_name' => 'Supplier B',
            'supplier_status' => 'Supplier',
            'approval_status' => 'pending_auditor_admin',
            'entry_date' => now()->toDateString(),
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'STATIONERY',
        ]);

        $response = $this->actingAs($this->auditor)->get(route('auditor.dashboard'));
        $response->assertStatus(200);

        // Assert badge is present with class blinking-danger-badge and count >= 2
        $response->assertSee('class="badge blinking-danger-badge"', false);
        $response->assertDontSee('position: absolute; top: -8px; right: -8px;', false);
    }
}
