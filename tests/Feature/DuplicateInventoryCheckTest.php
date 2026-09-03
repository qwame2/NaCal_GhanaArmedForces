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

    public function test_intra_payload_duplicate_check_blocks_multiple_entries_with_space_differences()
    {
        $user = User::create([
            'name' => 'Store Officer',
            'username' => 'store_officer_intra',
            'role' => 'Store Officer',
            'is_admin' => false,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        // Submit multiple items in the same payload where Row 1 is "A4SHEET" and Row 2 is "A4 SHEET"
        $response = $this->json('GET', route('api.inventory.check-duplicate'), [
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'description' => 'A4SHEET',
                    'qty' => '45'
                ],
                [
                    'description' => 'A4 SHEET',
                    'qty' => '45'
                ]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'duplicate' => true
        ]);
        $this->assertStringContainsString('Duplicate Entry Blocked', $response->json('message'));
        $this->assertStringContainsString('Row #2', $response->json('message'));
    }

    public function test_discrepancy_store_blocks_intra_payload_duplicate_items()
    {
        $user = User::create([
            'name' => 'Store Officer',
            'username' => 'store_officer_disc_dup',
            'role' => 'Store Officer',
            'is_admin' => false,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $this->actingAs($user);

        $payload = [
            'ledge_category' => 'A',
            'supplier_name' => 'Test Supplier',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'A',
                    'description' => 'A4SHEET',
                    'unit' => 'REAM',
                    'stock_balance' => '45',
                    'qty' => '45',
                    'variance' => '0',
                    'book_qty' => 45,
                    'store_location' => 'Store A',
                    'discrepancy_explanation' => 'None'
                ],
                [
                    'ledge_category' => 'A',
                    'description' => 'A4 SHEET',
                    'unit' => 'REAM',
                    'stock_balance' => '45',
                    'qty' => '45',
                    'variance' => '0',
                    'book_qty' => 45,
                    'store_location' => 'Store A',
                    'discrepancy_explanation' => 'None'
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.discrepancy.store'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);
        $this->assertStringContainsString('Duplicate Entry Blocked', $response->json('message'));
    }

    public function test_admin_can_remove_item_from_pending_entry_payload()
    {
        $admin = User::create([
            'name' => 'Head of Stores',
            'username' => 'head_of_stores_test',
            'role' => 'Head of Stores',
            'is_admin' => true,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $editReq = EditRequest::create([
            'user_id' => $admin->id,
            'item_id' => 0,
            'item_type' => 'batch_creation',
            'request_type' => 'sra_creation',
            'reason' => 'New Inventory Submission',
            'status' => 'pending',
            'payload' => json_encode([
                'ledge_category' => 'A',
                'items' => [
                    [
                        'description' => 'A4SHEET',
                        'unit' => 'REAM',
                        'qty' => '45',
                        'stock_balance' => '45'
                    ],
                    [
                        'description' => 'DUPLICATE ITEM TO REMOVE',
                        'unit' => 'PIECE(S)',
                        'qty' => '10',
                        'stock_balance' => '10'
                    ]
                ]
            ])
        ]);

        $this->actingAs($admin);

        // Remove item at index 1 ("DUPLICATE ITEM TO REMOVE")
        $response = $this->postJson(route('api.edit-requests.remove-item', ['id' => $editReq->id]), [
            'item_index' => 1,
            'description' => 'DUPLICATE ITEM TO REMOVE'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'remaining_count' => 1
        ]);

        $editReq->refresh();
        $payload = json_decode($editReq->payload, true);

        $this->assertCount(1, $payload['items']);
        $this->assertEquals('A4SHEET', $payload['items'][0]['description']);
    }

    public function test_admin_can_view_rollbacks_tab_and_cancel_rollback()
    {
        $admin = User::create([
            'name' => 'Head of Stores Admin',
            'username' => 'hos_admin_test',
            'role' => 'Head of Stores',
            'is_admin' => true,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $rollbackReq = EditRequest::create([
            'user_id' => $admin->id,
            'item_id' => 0,
            'item_type' => 'batch_creation',
            'request_type' => 'sra_creation',
            'reason' => 'Inventory Submission',
            'status' => 'rollback',
            'payload' => json_encode([
                'ledge_category' => 'A',
                'supplier_name' => 'Test Supplier',
                'items' => [
                    ['description' => 'TEST ITEM', 'qty' => 10]
                ]
            ]),
            'rollback_fields' => json_encode([
                'flagged' => ['supplier_name' => 'Incorrect supplier name'],
                'note' => 'Please fix supplier name'
            ])
        ]);

        $this->actingAs($admin);

        // 1. Verify Item Entry Approval page includes rollbacks
        $viewResponse = $this->get(route('stores.item-entry-approval'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Rollback List');
        $viewResponse->assertSee('RB-' . str_pad($rollbackReq->id, 5, '0', STR_PAD_LEFT));

        // 2. Admin cancels the rollback request
        $cancelResponse = $this->postJson(route('api.edit-requests.cancel-rollback', ['id' => $rollbackReq->id]));
        $cancelResponse->assertStatus(200);
        $cancelResponse->assertJson(['success' => true]);

        $rollbackReq->refresh();
        $this->assertEquals('pending', $rollbackReq->status);
    }

    public function test_store_officer_can_resubmit_rollback_without_field_locks()
    {
        $officer = User::create([
            'name' => 'Store Officer Test',
            'username' => 'officer_rollback_test',
            'role' => 'Store Officer',
            'is_admin' => false,
            'can_add_inventory' => true,
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        ]);

        $rollbackReq = EditRequest::create([
            'user_id' => $officer->id,
            'item_id' => 0,
            'item_type' => 'batch_creation',
            'request_type' => 'sra_creation',
            'reason' => 'New Inventory Entry Submission',
            'status' => 'rollback',
            'payload' => json_encode([
                'ledge_category' => 'A',
                'supplier_name' => 'Supplier XYZ',
                'supplier_status' => 'Full Delivery',
                'acquisition_type' => 'Supplier',
                'arrival_date' => date('Y-m-d'),
                'items' => [
                    [
                        'description' => 'A4 PAPER REAM',
                        'unit' => 'REAM',
                        'stock_balance' => '50',
                        'qty' => '50',
                        'variance' => '0',
                        'store_location' => 'Store A'
                    ],
                    [
                        'description' => 'PEN PACK',
                        'unit' => 'BOXES',
                        'stock_balance' => '20',
                        'qty' => '20',
                        'variance' => '0',
                        'store_location' => 'Store A'
                    ]
                ]
            ]),
            'rollback_fields' => json_encode([
                'flagged' => ['supplier_name' => 'Verify supplier name'],
                'note' => 'Please confirm supplier name',
                'items' => ['A4 PAPER REAM']
            ])
        ]);

        $this->actingAs($officer);

        // Store officer resubmits the rollback with all items maintained
        $resubmitData = [
            'rollback_id' => $rollbackReq->id,
            'ledge_category' => 'A',
            'supplier_name' => 'Supplier XYZ Corrected',
            'supplier_status' => 'Full Delivery',
            'acquisition_type' => 'Supplier',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'arrival_date' => date('Y-m-d'),
            'items' => [
                [
                    'ledge_category' => 'A',
                    'description' => 'A4 PAPER REAM',
                    'unit' => 'REAM',
                    'stock_balance' => '50',
                    'qty' => '50',
                    'variance' => '0',
                    'store_location' => 'Store A'
                ],
                [
                    'ledge_category' => 'A',
                    'description' => 'PEN PACK',
                    'unit' => 'BOXES',
                    'stock_balance' => '20',
                    'qty' => '20',
                    'variance' => '0',
                    'store_location' => 'Store A'
                ]
            ]
        ];

        $response = $this->postJson(route('inventory.store'), $resubmitData);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_pending' => true]);

        $rollbackReq->refresh();
        $this->assertEquals('resubmitted', $rollbackReq->status);

        $payload = json_decode($rollbackReq->payload, true);
        $this->assertCount(2, $payload['items']);
        $this->assertEquals('Supplier XYZ Corrected', $payload['supplier_name']);
        $this->assertEquals('A4 PAPER REAM', $payload['items'][0]['description']);
        $this->assertEquals('50', $payload['items'][0]['qty']);
        $this->assertEquals('50', $payload['items'][0]['stock_balance']);
        $this->assertEquals('PEN PACK', $payload['items'][1]['description']);
        $this->assertEquals('20', $payload['items'][1]['qty']);
    }
}
