<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EditRequest;
use App\Models\InventoryBatch;
use App\Models\Setting;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ItemEntryApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $headOfStores;
    private User $officerDelegated;
    private User $officerNormal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headOfStores = User::create([
            'name' => 'Anita Boison',
            'username' => 'anna',
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_admin' => 1,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        $this->officerDelegated = User::create([
            'name' => 'Linda Atta',
            'username' => 'atto',
            'role' => 'Officer',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_admin' => 0,
            'can_add_inventory' => 1,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        $this->officerNormal = User::create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'role' => 'Officer',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_admin' => 0,
            'can_add_inventory' => 1,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        Setting::set('delegated_approver_id', $this->officerDelegated->id);
    }

    public function test_officer_with_delegated_approver_status_must_submit_for_approval(): void
    {
        $this->actingAs($this->officerDelegated);
        $this->assertTrue($this->officerDelegated->isDelegatedApprover());

        $payload = [
            'ledge_category' => 'C',
            'supplier_name' => 'Test Supplier Ltd',
            'supplier_status' => 'Full Delivery',
            'donor_name' => null,
            'acquisition_type' => 'Supplier',
            'driver_name' => 'Driver John',
            'driver_phone' => '0240000000',
            'delivery_person' => 'Deliv Person',
            'delivery_phone' => '0540000000',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'C',
                    'description' => 'TONER 60B',
                    'serial_number' => null,
                    'unit' => 'BOXES',
                    'stock_balance' => '1',
                    'qty' => '1',
                    'variance' => '0',
                    'remarks' => 'Test entry',
                    'store_location' => 'STORE A',
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_pending' => true,
        ]);

        // Assert no batch was created directly in inventory_batches
        $this->assertDatabaseMissing('inventory_batches', [
            'supplier_name' => 'Test Supplier Ltd',
        ]);

        // Assert an EditRequest was created in pending state
        $editReq = EditRequest::where('user_id', $this->officerDelegated->id)
            ->where('item_type', 'batch_creation')
            ->where('request_type', 'sra_creation')
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($editReq);

        // Assert Head of Stores received the approval notification
        $this->assertDatabaseHas('messages', [
            'receiver_id' => $this->headOfStores->id,
            'sender_id' => $this->officerDelegated->id,
            'edit_request_id' => $editReq->id,
        ]);

        // Assert submitter did NOT send an approval notification to themselves
        $this->assertDatabaseMissing('messages', [
            'receiver_id' => $this->officerDelegated->id,
            'sender_id' => $this->officerDelegated->id,
            'edit_request_id' => $editReq->id,
        ]);

        // Verify it appears in Item Entry Approval Panel for Head of Stores
        $panelResponse = $this->actingAs($this->headOfStores)->get(route('stores.item-entry-approval'));
        $panelResponse->assertStatus(200);
        $panelResponse->assertSee('TONER 60B');
        $panelResponse->assertSee('Linda Atta');
    }

    public function test_normal_officer_submits_for_approval(): void
    {
        $this->actingAs($this->officerNormal);

        $payload = [
            'ledge_category' => 'A',
            'supplier_name' => 'Stationery Supplies',
            'supplier_status' => 'Full Delivery',
            'donor_name' => null,
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'A',
                    'description' => 'A4 NOTEPADS',
                    'serial_number' => null,
                    'unit' => 'PACKS',
                    'stock_balance' => '50',
                    'qty' => '50',
                    'variance' => '0',
                    'remarks' => null,
                    'store_location' => 'SHELF 1',
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_pending' => true,
        ]);

        $this->assertDatabaseHas('edit_requests', [
            'user_id' => $this->officerNormal->id,
            'item_type' => 'batch_creation',
            'status' => 'pending',
        ]);
    }

    public function test_officer_with_delegated_status_submits_discrepancy_for_approval(): void
    {
        $this->actingAs($this->officerDelegated);

        $payload = [
            'ledge_category' => 'B',
            'supplier_name' => 'Cleaners Ltd',
            'supplier_status' => 'Full Delivery',
            'donor_name' => null,
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'B',
                    'description' => 'BLEACH 5L',
                    'serial_number' => null,
                    'unit' => 'GALLON(S)',
                    'book_qty' => '10',
                    'stock_balance' => '10',
                    'qty' => '10',
                    'variance' => '0',
                    'remarks' => null,
                    'store_location' => 'WAREHOUSE B',
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.discrepancy.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_pending' => true,
        ]);

        $this->assertDatabaseHas('edit_requests', [
            'user_id' => $this->officerDelegated->id,
            'item_type' => 'batch_creation',
            'request_type' => 'discrepancy_creation',
            'status' => 'pending',
        ]);
    }

    public function test_head_of_stores_entry_is_saved_directly(): void
    {
        $this->actingAs($this->headOfStores);

        $payload = [
            'ledge_category' => 'A',
            'supplier_name' => 'Direct Store Supplies',
            'supplier_status' => 'Full Delivery',
            'donor_name' => null,
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'A',
                    'description' => 'MARKER PENS',
                    'serial_number' => null,
                    'unit' => 'BOXES',
                    'stock_balance' => '10',
                    'qty' => '10',
                    'variance' => '0',
                    'remarks' => null,
                    'store_location' => 'SHELF A',
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertFalse($response->json('is_pending') ?? false);

        $this->assertDatabaseHas('inventory_batches', [
            'supplier_name' => 'Direct Store Supplies',
            'approval_status' => 'pending_auditor_admin',
            'approved_by' => $this->headOfStores->id,
        ]);
    }
}
