<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repair inventory_batches rows where supplier_status is NULL or empty.
     * MySQL's != operator excludes NULLs, so those batches are invisible to all
     * queries filtering with: WHERE supplier_status != 'System Draft'
     */
    public function up(): void
    {
        DB::table('inventory_batches')
            ->where(function ($q) {
                $q->whereNull('supplier_status')
                  ->orWhere('supplier_status', '');
            })
            ->update(['supplier_status' => 'Full Delivery']);
    }

    public function down(): void
    {
        // Not reversible — we don't know which rows were NULL originally
    }
};

