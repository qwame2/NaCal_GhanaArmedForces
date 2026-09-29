<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\InventoryItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('inventory_items')) {
            $items = InventoryItem::all();
            foreach ($items as $item) {
                $originalReceived = $item->original_received_qty ?? ($item->received_qty ?? $item->qty);

                if (empty($item->received_qty) || (float)$item->received_qty <= 0) {
                    $item->received_qty = $originalReceived;
                }

                $item->qty = (string)$item->received_qty;
                $item->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data alignment migration; no destructive reversal needed.
    }
};
