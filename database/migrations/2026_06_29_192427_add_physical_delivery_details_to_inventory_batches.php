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
        try {
            \App\Models\InventoryBatch::selfHealSchema();
        } catch (\Exception $e) {
            // Ignore self heal failures during migration boot
        }

        Schema::table('inventory_batches', function (Blueprint $table) {
            $afterCol = Schema::hasColumn('inventory_batches', 'delivery_phone') ? 'delivery_phone' : 'arrival_date';
            $table->string('driver_name')->nullable()->after($afterCol);
            $table->string('driver_phone')->nullable()->after('driver_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'driver_phone']);
        });
    }
};
