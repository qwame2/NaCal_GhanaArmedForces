<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Setting;

class RequisitionItemsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_requisition_page_shows_items_from_all_active_non_rejected_batches()
    {
        $user = User::factory()->create([
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        // Batch 1: Approved
        $batch1 = InventoryBatch::create([
            'sra_number' => 'SRA-VIS-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
            'supplier_name' => 'Supplier 1',
        ]);

        // Batch 2: Stores Approved
        $batch2 = InventoryBatch::create([
            'sra_number' => 'SRA-VIS-002',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'B',
            'approval_status' => 'stores_approved',
            'supplier_status' => 'Full Delivery',
            'supplier_name' => 'Supplier 2',
        ]);

        // Batch 3: Pending Stores batch
        $batch3 = InventoryBatch::create([
            'sra_number' => 'SRA-VIS-003',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'C',
            'approval_status' => 'pending_stores',
            'supplier_status' => 'Partial Delivery',
            'supplier_name' => 'Supplier 3',
        ]);

        InventoryItem::create([
            'batch_id' => $batch1->id,
            'description' => 'A4 SHEET',
            'unit' => 'reams',
            'stock_balance' => '100',
        ]);

        InventoryItem::create([
            'batch_id' => $batch2->id,
            'description' => 'CLEANING SOAP',
            'unit' => 'PACK',
            'stock_balance' => '50',
        ]);

        InventoryItem::create([
            'batch_id' => $batch3->id,
            'description' => 'HDMI CABLE',
            'unit' => 'PIECE',
            'stock_balance' => '15',
        ]);

        $response = $this->actingAs($user)->get(route('requisitions.index'));
        $response->assertStatus(200);

        $availableItems = $response->viewData('availableItems');
        $this->assertNotNull($availableItems);

        $descriptions = $availableItems->pluck('description')->toArray();

        $this->assertContains('A4 SHEET', $descriptions);
        $this->assertContains('CLEANING SOAP', $descriptions);
        $this->assertContains('HDMI CABLE', $descriptions);
    }

    public function test_category_normalization_and_cross_category_item_separation()
    {
        $user = User::factory()->create([
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        // Batch A with category code 'A'
        $batchA = InventoryBatch::create([
            'sra_number' => 'SRA-CAT-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
        ]);

        // Batch B with category name 'Cleaning' (should map to 'B')
        $batchB = InventoryBatch::create([
            'sra_number' => 'SRA-CAT-002',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'Cleaning',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
        ]);

        InventoryItem::create([
            'batch_id' => $batchA->id,
            'description' => 'MULTIPLE PURPOSE CLEANER',
            'unit' => 'BOTTLE',
            'stock_balance' => '10',
        ]);

        InventoryItem::create([
            'batch_id' => $batchB->id,
            'description' => 'MULTIPLE PURPOSE CLEANER',
            'unit' => 'BOTTLE',
            'stock_balance' => '25',
        ]);

        $response = $this->actingAs($user)->get(route('requisitions.index'));
        $response->assertStatus(200);

        $availableItems = $response->viewData('availableItems');

        $itemA = $availableItems->first(fn($i) => $i->ledge_category === 'A');
        $itemB = $availableItems->first(fn($i) => $i->ledge_category === 'B');

        $this->assertNotNull($itemA);
        $this->assertNotNull($itemB);
        $this->assertEquals(10, $itemA->total_stock);
        $this->assertEquals(25, $itemB->total_stock);
    }

    public function test_a5_envelopes_shows_on_requisition_page()
    {
        $user = User::factory()->create([
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-ENV-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A5 ENVELOPES',
            'unit' => 'PACK',
            'stock_balance' => '50',
        ]);

        $response = $this->actingAs($user)->get(route('requisitions.index'));
        $response->assertStatus(200);

        $response->assertSee('A5 ENVELOPES');

        $availableItems = $response->viewData('availableItems');
        $envelopeItem = $availableItems->firstWhere('description', 'A5 ENVELOPES');

        $this->assertNotNull($envelopeItem);
        $this->assertEquals(50, $envelopeItem->total_stock);
    }

    public function test_blue_pens_and_bluepens_spacing_consolidation()
    {
        $user = User::factory()->create([
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        $batch1 = InventoryBatch::create([
            'sra_number' => 'SRA-PEN-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
        ]);

        $batch2 = InventoryBatch::create([
            'sra_number' => 'SRA-PEN-002',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'Stationary', // Category name 'Stationary' resolves to 'A'
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
        ]);

        InventoryItem::create([
            'batch_id' => $batch1->id,
            'description' => 'BLUE PENS',
            'unit' => 'PACK',
            'stock_balance' => '40',
        ]);

        InventoryItem::create([
            'batch_id' => $batch2->id,
            'description' => 'BLUEPENS',
            'unit' => 'PACK',
            'stock_balance' => '60',
        ]);

        $response = $this->actingAs($user)->get(route('requisitions.index'));
        $response->assertStatus(200);

        $availableItems = $response->viewData('availableItems');
        
        // They should be consolidated into one item with combined stock of 100
        $penItem = $availableItems->first(fn($i) => Setting::isExactOrTypoMatch($i->description, 'BLUE PENS'));
        $this->assertNotNull($penItem, 'BLUE PENS or BLUEPENS should exist in available items');
        $this->assertEquals(100, $penItem->total_stock, 'Stock of BLUE PENS and BLUEPENS should be combined to 100');
    }
}
