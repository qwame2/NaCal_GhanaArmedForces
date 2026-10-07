<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Setting;

class RequisitionItemsDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_requisition_page_shows_all_distinct_store_items()
    {
        $user = User::factory()->create([
            'role' => 'Requisitioner',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-TEST-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
            'supplier_name' => 'Test Supplier',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'HP',
            'unit' => 'PACK',
            'stock_balance' => '20',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'HP LAPEE',
            'unit' => 'PACK',
            'stock_balance' => '5',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 SHEET',
            'unit' => 'PACK',
            'stock_balance' => '50',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 SHHET',
            'unit' => 'PACK',
            'stock_balance' => '10',
        ]);

        $response = $this->actingAs($user)->get(route('requisitions.index'));
        $response->assertStatus(200);

        $availableItems = $response->viewData('availableItems');
        $this->assertNotNull($availableItems);

        $descriptions = $availableItems->pluck('description')->toArray();

        // Distinct items HP and HP LAPEE must both exist separately
        $this->assertContains('HP', $descriptions);
        $this->assertContains('HP LAPEE', $descriptions);

        // Distinct items A4 SHEET and A4 SHHET exist separately with their own stocks
        $a4Item = $availableItems->firstWhere('description', 'A4 SHEET');
        $a4ShhetItem = $availableItems->firstWhere('description', 'A4 SHHET');

        $this->assertNotNull($a4Item);
        $this->assertNotNull($a4ShhetItem);
        $this->assertEquals(50, $a4Item->total_stock);
        $this->assertEquals(10, $a4ShhetItem->total_stock);
    }
}
