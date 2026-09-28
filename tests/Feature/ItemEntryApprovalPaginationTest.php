<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EditRequest;
use App\Models\InventoryBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ItemEntryApprovalPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_approval_tab_displays_pagination_controls(): void
    {
        $headOfStores = User::create([
            'name' => 'Anita Boison',
            'username' => 'head_of_stores',
            'role' => 'Head of Stores',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_admin' => 1,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        $officer = User::create([
            'name' => 'Officer One',
            'username' => 'officer_one',
            'role' => 'Officer',
            'department' => 'Stores',
            'registration_status' => 'approved',
            'is_admin' => 0,
            'is_active' => true,
            'password' => Hash::make('Password123'),
        ]);

        // Create 3 pending requests
        for ($i = 1; $i <= 3; $i++) {
            $batch = InventoryBatch::create([
                'ledge_category' => 'A',
                'supplier_name' => "Supplier {$i}",
                'acquisition_type' => 'Supplier',
                'entry_date' => now()->subMinutes($i)->format('Y-m-d H:i:s'),
                'arrival_date' => now()->format('Y-m-d'),
                'approval_status' => 'pending',
            ]);

            EditRequest::create([
                'user_id' => $officer->id,
                'item_id' => $batch->id,
                'item_type' => 'batch_creation',
                'request_type' => 'batch_creation',
                'reason' => "New stock entry #{$i}",
                'status' => 'pending',
                'payload' => json_encode([
                    'supplier_name' => "Supplier {$i}",
                    'items' => [
                        ['description' => "Item Alpha {$i}", 'quantity' => 10]
                    ]
                ]),
            ]);
        }

        // 1. Visit with default pagination
        $response = $this->actingAs($headOfStores)->get(route('stores.item-entry-approval'));
        $response->assertStatus(200);
        $response->assertSee('pagination-footer');
        $response->assertSee('Total Records');
        $response->assertSee('Showing');

        // 2. Visit with per_page = 2 (so page 1 shows 2 items, page 2 has 1 item)
        $respPage1 = $this->actingAs($headOfStores)->get(route('stores.item-entry-approval', ['pending_per_page' => 2, 'pending_page' => 1]));
        $respPage1->assertStatus(200);
        $respPage1->assertSee('pagination-footer');
        $respPage1->assertSee('Showing 1 - 2 of');
        $respPage1->assertSee('pending_page=2');

        // 3. Visit page 2
        $respPage2 = $this->actingAs($headOfStores)->get(route('stores.item-entry-approval', ['pending_per_page' => 2, 'pending_page' => 2]));
        $respPage2->assertStatus(200);
        $respPage2->assertSee('Showing 3 - 3 of');
        $respPage2->assertSee('pending_page=1');

        // 4. Test AJAX polling returns pagination HTML
        $ajaxResp = $this->actingAs($headOfStores)->getJson(route('stores.item-entry-approval', ['pending_per_page' => 2, 'pending_page' => 1]));
        $ajaxResp->assertStatus(200);
        $ajaxResp->assertJsonStructure(['pending_count', 'pending_html', 'edits_html', 'rollbacks_html']);
        $this->assertStringContainsString('pagination-footer', $ajaxResp->json('pending_html'));
    }
}
