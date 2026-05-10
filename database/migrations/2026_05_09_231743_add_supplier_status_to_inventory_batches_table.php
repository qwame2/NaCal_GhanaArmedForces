<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->string('supplier_status')->nullable()->after('supplier_name');
        });

        // Optional: Data Migration to split existing supplier_name values
        $batches = \Illuminate\Support\Facades\DB::table('inventory_batches')->get();
        foreach ($batches as $batch) {
            if ($batch->supplier_name && str_contains($batch->supplier_name, '[')) {
                $parts = explode('[', $batch->supplier_name);
                $cleanName = trim($parts[0]);
                $status = trim(str_replace(']', '', $parts[1]));
                
                \Illuminate\Support\Facades\DB::table('inventory_batches')
                    ->where('id', $batch->id)
                    ->update([
                        'supplier_name' => $cleanName,
                        'supplier_status' => $status
                    ]);
            } elseif ($batch->acquisition_type === 'Donor') {
                \Illuminate\Support\Facades\DB::table('inventory_batches')
                    ->where('id', $batch->id)
                    ->update([
                        'supplier_status' => 'Donor'
                    ]);
            } else {
                \Illuminate\Support\Facades\DB::table('inventory_batches')
                    ->where('id', $batch->id)
                    ->update([
                        'supplier_status' => 'Full Delivery'
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn('supplier_status');
        });
    }
};
