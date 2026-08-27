<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EditRequest;
use App\Models\InventoryBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateInventoryCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_insensitive_duplicate_check_blocks_matching_pending_requests()
    {
        $user = User::create([
            'name' => 'Store Officer',
            'username' => 'store_officer',
            'role' => 'Store Officer',
            'is_admin' => false,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Create a pending EditRequest with item "LASERJET 523X"
        EditRequest::create([
            'user_id' => $user->id,
            'item_id' => 0,
            'item_type' => 'batch_creation',
            'request_type' => 'sra_creation',
            'reason' => 'New Inventory Entry Submission',
            'status' => 'pending',
            'payload' => json_encode([
                'ledge_category' => 'C',
                'items' => [
                    [
                        'ledge_category' => 'C',
                        'description' => 'LASERJET 523X',
                        'qty' => '4',
                        'stock_balance' => '4',
                        'unit' => 'PIECE(S)',
                        'store_location' => 'Store A',
                        'book_qty' => '0',
                        'variance' => '4'
                    ]
                ]
            ])
        ]);

        // Try submitting "LASERJET523X" (no space) with same quantity
        $response = $this->json('GET', route('api.inventory.check-duplicate'), [
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'description' => 'LASERJET523X',
                    'qty' => '4'
                ]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'duplicate' => true
        ]);
        $this->assertStringContainsString('Duplicate Entry Blocked', $response->json('message'));
    }

    public function test_space_insensitive_duplicate_check_blocks_matching_recent_batches()
    {
        $user = User::create([
            'name' => 'Store Officer',
            'username' => 'store_officer',
            'role' => 'Store Officer',
            'is_admin' => true,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Create a recent inventory batch with item "LASERJET 523X"
        $batch = InventoryBatch::create([
            'ledge_category' => 'C',
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'arrival_date' => date('Y-m-d'),
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'approval_status' => 'approved',
            'created_at' => now(),
        ]);

        $batch->items()->create([
            'description' => 'LASERJET 523X',
            'unit' => 'PIECE(S)',
            'store_location' => 'Store A',
            'qty' => '4',
            'stock_balance' => '4',
            'variance' => '4',
            'book_qty' => '0',
        ]);

        // Try submitting "LASERJET523X" (no space) with same quantity
        $response = $this->json('GET', route('api.inventory.check-duplicate'), [
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'description' => 'LASERJET523X',
                    'qty' => '4'
                ]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'duplicate' => true
        ]);
        $this->assertStringContainsString('Duplicate Entry Blocked', $response->json('message'));
    }

    public function test_store_officer_inventory_discrepancy_submission_requires_approval()
    {
        $user = User::create([
            'name' => 'Store Officer User',
            'username' => 'store_officer_user',
            'role' => 'Store Officer',
            'department' => 'Stores',
            'is_admin' => false,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Make a discrepancy submission request as the Store Officer
        $payload = [
            'ledge_category' => 'C',
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'C',
                    'description' => 'TEST ITEM',
                    'unit' => 'PIECE(S)',
                    'stock_balance' => '10',
                    'qty' => '10',
                    'variance' => '10',
                    'book_qty' => 0,
                    'store_location' => 'Store A',
                    'discrepancy_explanation' => 'Counting Error'
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.discrepancy.store'), $payload);

        // The response should indicate it was successfully submitted but is pending admin approval
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_pending' => true,
        ]);

        // Verify that it is stored in edit_requests table with status 'pending'
        $this->assertDatabaseHas('edit_requests', [
            'user_id' => $user->id,
            'request_type' => 'discrepancy_creation',
            'status' => 'pending',
        ]);
    }
}
