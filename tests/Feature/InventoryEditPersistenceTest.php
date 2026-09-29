<?php

namespace Tests\Feature;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryEditPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private User $headOfStores;
    private User $storeOfficer;
    private User $mainAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headOfStores = User::factory()->create([
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'is_admin' => 1,
            'can_add_inventory' => true,
        ]);

        $this->storeOfficer = User::factory()->create([
            'role' => 'Store Officer',
            'department' => 'Stores',
            'is_admin' => 0,
            'can_add_inventory' => true,
        ]);

        $this->mainAdmin = User::factory()->create([
            'role' => 'Main Admin',
            'department' => 'Administration',
            'is_admin' => 1,
            'can_add_inventory' => true,
        ]);
    }

    /**
     * Test case required by user:
     * Before: TONER 59A (BLACK)
     * After editing: TONER 59A
     * Both frontend response and database must contain TONER 59A.
     */
    public function test_editing_toner_description_and_fields_persists_to_database(): void
    {
        $batch = InventoryBatch::create([
            'entry_date' => now(),
            'arrival_date' => now()->toDateString(),
            'supplier_name' => 'HP Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'ledge_category' => 'C',
            'recorded_by' => $this->headOfStores->id,
            'approval_status' => 'approved',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'TONER 59A (BLACK)',
            'qty' => 10,
            'received_qty' => 10,
            'stock_balance' => 10,
            'unit' => 'BOXES',
            'store_location' => 'STORE A',
            'remarks' => '',
            'variance' => 0,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'description' => 'TONER 59A (BLACK)',
            'qty' => 10,
            'stock_balance' => 10,
        ]);

        // Head of Stores edits item to TONER 59A, changes qty to 15, unit to PACKS, location to STORE B
        $response = $this->actingAs($this->headOfStores)->putJson("/received-items/{$batch->id}", [
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'C',
            'acquisition_type' => 'Supplier',
            'supplier_name' => 'HP Supplier',
            'supplier_status' => 'Full Delivery',
            'items' => [
                [
                    'id' => $item->id,
                    'description' => 'TONER 59A',
                    'qty' => 15,
                    'unit' => 'PACKS',
                    'stock_balance' => 15,
                    'variance' => 0,
                    'store_location' => 'STORE B',
                    'remarks' => 'Count confirmed',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify database is updated
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'description' => 'TONER 59A',
            'qty' => 15,
            'received_qty' => 15,
            'unit' => 'PACKS',
            'stock_balance' => 15,
            'store_location' => 'STORE B',
            'remarks' => 'Count confirmed',
        ]);

        $this->assertDatabaseMissing('inventory_items', [
            'id' => $item->id,
            'description' => 'TONER 59A (BLACK)',
        ]);

        // Fresh reload
        $freshItem = $item->fresh();
        $this->assertEquals('TONER 59A', $freshItem->description);
        $this->assertEquals(15, (float)$freshItem->qty);
        $this->assertEquals('PACKS', $freshItem->unit);
        $this->assertEquals(15, (float)$freshItem->stock_balance);
        $this->assertEquals('STORE B', $freshItem->store_location);
    }

    public function test_store_officer_can_persist_item_edits(): void
    {
        $batch = InventoryBatch::create([
            'entry_date' => now(),
            'arrival_date' => now()->toDateString(),
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'ledge_category' => 'A',
            'recorded_by' => $this->storeOfficer->id,
            'approval_status' => 'approved',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 Paper',
            'qty' => 50,
            'received_qty' => 50,
            'stock_balance' => 50,
            'unit' => 'REAMS',
            'store_location' => 'STORE A',
            'remarks' => '',
            'variance' => 0,
        ]);

        $response = $this->actingAs($this->storeOfficer)->putJson("/received-items/{$batch->id}", [
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'acquisition_type' => 'Supplier',
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'items' => [
                [
                    'id' => $item->id,
                    'description' => 'A4 Paper (80gsm)',
                    'qty' => 45,
                    'unit' => 'BOXES',
                    'stock_balance' => 45,
                    'variance' => 0,
                    'store_location' => 'STORE A',
                    'remarks' => 'Adjusted brand',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $item->fresh();
        $this->assertEquals('A4 Paper (80gsm)', $fresh->description);
        $this->assertEquals(45, (float)$fresh->qty);
        $this->assertEquals('BOXES', $fresh->unit);
    }

    public function test_main_admin_can_persist_item_edits(): void
    {
        $batch = InventoryBatch::create([
            'entry_date' => now(),
            'arrival_date' => now()->toDateString(),
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'ledge_category' => 'B',
            'recorded_by' => $this->headOfStores->id,
            'approval_status' => 'approved',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'Ballpoint Pens',
            'qty' => 100,
            'received_qty' => 100,
            'stock_balance' => 100,
            'unit' => 'PACKS',
            'store_location' => 'STORE A',
        ]);

        $response = $this->actingAs($this->mainAdmin)->putJson("/received-items/{$batch->id}", [
            'arrival_date' => now()->toDateString(),
            'ledge_category' => 'B',
            'acquisition_type' => 'Supplier',
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'items' => [
                [
                    'id' => $item->id,
                    'description' => 'Ballpoint Pens (Blue)',
                    'qty' => 100,
                    'unit' => 'BOXES',
                    'stock_balance' => 100,
                    'store_location' => 'STORE B',
                    'remarks' => 'Admin color clarification',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $item->fresh();
        $this->assertEquals('Ballpoint Pens (Blue)', $fresh->description);
        $this->assertEquals('BOXES', $fresh->unit);
        $this->assertEquals('STORE B', $fresh->store_location);
    }

    public function test_direct_item_update_endpoint_persists_to_database(): void
    {
        $batch = InventoryBatch::create([
            'entry_date' => now(),
            'arrival_date' => now()->toDateString(),
            'supplier_name' => 'Direct Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'ledge_category' => 'C',
            'recorded_by' => $this->headOfStores->id,
            'approval_status' => 'approved',
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'USB Cable',
            'qty' => 20,
            'received_qty' => 20,
            'stock_balance' => 20,
            'unit' => 'PIECES',
            'store_location' => 'STORE A',
        ]);

        $response = $this->actingAs($this->headOfStores)->putJson("/inventory/items/{$item->id}", [
            'description' => 'USB-C Fast Cable',
            'qty' => 25,
            'unit' => 'PACKS',
            'stock_balance' => 25,
            'store_location' => 'STORE B',
            'remarks' => 'Updated via direct item API',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $item->fresh();
        $this->assertEquals('USB-C Fast Cable', $fresh->description);
        $this->assertEquals(25, (float)$fresh->qty);
        $this->assertEquals('PACKS', $fresh->unit);
        $this->assertEquals('STORE B', $fresh->store_location);
        $this->assertEquals('Updated via direct item API', $fresh->remarks);
    }
}
