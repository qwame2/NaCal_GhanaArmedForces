<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnitConversionRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $cols = [
            'origin_admin_status' => fn($table) => $table->string('origin_admin_status')->default('pending'),
            'origin_approved_by' => fn($table) => $table->unsignedBigInteger('origin_approved_by')->nullable(),
            'main_admin_status' => fn($table) => $table->string('main_admin_status')->default('pending'),
            'stores_approved_by' => fn($table) => $table->unsignedBigInteger('stores_approved_by')->nullable(),
            'requires_dg_approval' => fn($table) => $table->boolean('requires_dg_approval')->default(false),
            'dg_status' => fn($table) => $table->string('dg_status')->nullable(),
        ];

        foreach ($cols as $col => $callback) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', $col)) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) use ($callback) {
                    $callback($table);
                });
            }
        }
    }

    public function test_admin_can_store_and_destroy_unit_conversion_rule()
    {
        $admin = User::factory()->create([
            'role' => 'Main Admin',
            'is_admin' => true,
            'is_active' => true,
            'registration_status' => 'approved'
        ]);

        $this->actingAs($admin);

        // 1. Store conversion rule
        $response = $this->post(route('admin.settings.unit-conversion-rule.store'), [
            'category' => 'A',
            'keywords' => ['A4 sheets'],
            'received_unit' => 'Boxes',
            'requisition_unit' => 'Reams',
            'conversion_factor' => 5
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rule = Setting::getUnitConversionRule('A4 sheets');
        $this->assertNotNull($rule);
        $this->assertEquals('Boxes', $rule['received_unit']);
        $this->assertEquals('Reams', $rule['requisition_unit']);
        $this->assertEquals(5.0, $rule['conversion_factor']);

        // 2. Destroy conversion rule
        $delResponse = $this->delete(route('admin.settings.unit-conversion-rule.destroy'), [
            'keyword' => 'a4 sheets'
        ]);

        $delResponse->assertRedirect();
        $delResponse->assertSessionHas('success');

        $ruleAfter = Setting::getUnitConversionRule('A4 sheets');
        $this->assertNull($ruleAfter);
    }

    public function test_stock_balance_formatting_with_unit_conversion()
    {
        Setting::set('unit_conversion_rules', [
            'a4 sheets' => [
                'category' => 'A',
                'received_unit' => 'Boxes',
                'requisition_unit' => 'Reams',
                'conversion_factor' => 5,
                'description' => 'A4 sheets'
            ]
        ]);

        // 10 Boxes initial -> 50 Reams
        $displayInitial = Setting::formatStockBalanceWithConversion(10, 'A4 sheets', 'A');
        $this->assertEquals('10 Boxes<br><span style="font-size: 0.78rem; font-weight: 700; color: #0284c7;">(50 Reams)</span>', $displayInitial);

        // 9.4 Boxes after 3 Reams issued -> 47 Reams -> 9 Boxes and 2 Reams
        $displayAfterIssuance = Setting::formatStockBalanceWithConversion(9.4, 'A4 sheets', 'A');
        $this->assertEquals('9 Boxes and 2 Reams<br><span style="font-size: 0.78rem; font-weight: 700; color: #0284c7;">(47 Reams)</span>', $displayAfterIssuance);
    }

    public function test_requisition_flow_converts_units_and_deducts_fractional_stock()
    {
        $admin = User::factory()->create([
            'role' => 'Main Admin',
            'is_admin' => true,
            'is_active' => true,
            'registration_status' => 'approved'
        ]);

        $requisitioner = User::factory()->create([
            'role' => 'Requisitioner',
            'is_admin' => false,
            'is_active' => true,
            'can_make_requisition' => true,
            'department' => 'Operations',
            'name' => 'John Requisitioner',
            'username' => 'johnreq',
            'phone' => '0241234567',
            'service_number' => 'SN12345'
        ]);

        // Define unit conversion rule: 1 Box = 5 Reams
        Setting::set('unit_conversion_rules', [
            'a4 sheets' => [
                'category' => 'A',
                'received_unit' => 'Boxes',
                'requisition_unit' => 'Reams',
                'conversion_factor' => 5,
                'description' => 'A4 sheets'
            ]
        ]);

        // Receive 10 Boxes of A4 sheets into stock
        $batch = InventoryBatch::create([
            'reference_number' => 'SRA-00100',
            'supplier_name' => 'Paper Co',
            'entry_date' => '2026-10-01',
            'recorder_id' => $admin->id,
            'approval_status' => 'approved',
            'supplier_status' => 'Verified',
            'ledge_category' => 'A'
        ]);

        $item = InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 sheets',
            'unit' => 'Boxes',
            'qty' => 10,
            'received_qty' => 10,
            'original_received_qty' => 10,
            'stock_balance' => 10
        ]);

        // Requisitioner requests 3 Reams
        $this->actingAs($requisitioner);
        $reqResponse = $this->postJson(route('requisitions.store'), [
            'requester_name' => 'John Requisitioner',
            'department' => 'Operations',
            'purpose' => 'Printing documents',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'A4 sheets',
                    'category' => 'A',
                    'unit' => 'Reams',
                    'quantity_requested' => 3
                ]
            ]
        ]);

        $reqResponse->assertStatus(200);
        $reqResponse->assertJson(['success' => true]);

        $requisition = StoreRequisition::latest('id')->first();
        $this->assertNotNull($requisition);

        // Approve the requisition for 3 Reams
        $this->actingAs($admin);

        // Main Admin Approval
        $requisition->update([
            'main_admin_status' => 'approved',
            'main_admin_id' => $admin->id
        ]);

        // Final store approval and deduction
        $updateResp = $this->post(route('admin.requisitions.process', $requisition->id), [
            'status' => 'approved',
            'items' => [
                [
                    'id' => $requisition->items->first()->id,
                    'quantity_approved' => 3
                ]
            ]
        ]);

        $updateResp->assertStatus(200);
        $updateResp->assertJson(['success' => true]);

        // Fresh item stock check
        $freshItem = InventoryItem::find($item->id);
        // 10 Boxes - (3 Reams / 5 Reams/Box) = 10 - 0.6 = 9.4 Boxes remaining!
        $this->assertEquals(9.4, (float)$freshItem->stock_balance);

        // Verify stock formatting display
        $formatted = Setting::formatStockBalanceWithConversion($freshItem->stock_balance, $freshItem->description, $freshItem->batch->ledge_category);
        $this->assertEquals('9 Boxes and 2 Reams<br><span style="font-size: 0.78rem; font-weight: 700; color: #0284c7;">(47 Reams)</span>', $formatted);
    }

    public function test_admin_show_calculates_stock_in_converted_requisition_units()
    {
        Setting::set('unit_conversion_rules', [
            'a4 paper' => [
                'category' => 'A',
                'received_unit' => 'Boxes',
                'requisition_unit' => 'Reams',
                'conversion_factor' => 6,
                'description' => 'A4 Paper'
            ]
        ]);

        $admin = User::factory()->create([
            'role' => 'Main Admin',
            'is_admin' => true,
            'is_active' => true,
            'registration_status' => 'approved'
        ]);

        $batch = InventoryBatch::create([
            'supplier_name' => 'Supplier Co',
            'supplier_status' => 'Received',
            'approval_status' => 'approved',
            'ledge_category' => 'A',
            'entry_date' => now()->toDateString()
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 Paper',
            'unit' => 'Boxes',
            'qty' => 28,
            'stock_balance' => 28,
        ]);

        $requisition = StoreRequisition::create([
            'unique_id' => 'REQ-99999',
            'requested_by' => $admin->id,
            'requester_name' => $admin->name,
            'department' => 'IT Department',
            'purpose' => 'Printing',
            'priority' => 'normal',
            'status' => 'pending'
        ]);

        StoreRequisitionItem::create([
            'requisition_id' => $requisition->id,
            'description' => 'A4 Paper',
            'category' => 'A',
            'unit' => 'Reams',
            'quantity_requested' => 168
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.requisitions.show', $requisition->id));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data['items']);
        $itemData = $data['items'][0];

        $this->assertEquals(168.0, (float)$itemData['current_stock']);
        $this->assertEquals('Reams', $itemData['unit']);
        $this->assertTrue($itemData['stock_sufficient']);
    }
}
