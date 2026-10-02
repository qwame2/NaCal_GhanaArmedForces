<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;

class ItemRemarksJustificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_item_remarks_move_along_with_justification()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'phone' => '0241234567',
            'service_number' => 'SN-12345',
            'role' => 'Requisitioner',
            'department' => 'IT',
            'registration_status' => 'approved',
            'can_make_requisition' => true,
        ]);

        $batch = InventoryBatch::create([
            'sra_number' => 'SRA-REMARK-001',
            'entry_date' => now()->toDateString(),
            'ledge_category' => 'A',
            'approval_status' => 'approved',
            'supplier_status' => 'Full Delivery',
            'supplier_name' => 'Supplier XYZ',
        ]);

        InventoryItem::create([
            'batch_id' => $batch->id,
            'description' => 'A4 PAPER',
            'unit' => 'PACK',
            'stock_balance' => '100',
        ]);

        $payload = [
            'requester_name' => $user->name,
            'department' => 'IT',
            'rank_or_title' => 'Specialist',
            'purpose' => 'Quarterly documentation printouts.',
            'priority' => 'normal',
            'usage_type' => 'permanent',
            'items' => [
                [
                    'description' => 'A4 PAPER',
                    'category' => 'A',
                    'unit' => 'PACK',
                    'quantity_requested' => 5,
                    'remarks' => '80gsm Heavy Duty White Paper'
                ]
            ]
        ];

        $response = $this->actingAs($user)->postJson(route('requisitions.store'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $reqId = $response->json('id');
        $this->assertNotNull($reqId);

        $this->assertDatabaseHas('store_requisition_items', [
            'requisition_id' => $reqId,
            'description' => 'A4 PAPER',
            'remarks' => '80gsm Heavy Duty White Paper'
        ]);

        $requisition = \App\Models\StoreRequisition::find($reqId);
        $this->assertEquals('Quarterly documentation printouts.', $requisition->purpose);
        $this->assertEquals('80gsm Heavy Duty White Paper', $requisition->items->first()->remarks);
    }
}
