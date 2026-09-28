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
        if (Schema::hasTable('inventory_items') && !Schema::hasColumn('inventory_items', 'received_qty')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->decimal('received_qty', 15, 2)->nullable()->after('unit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items', 'received_qty')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropColumn('received_qty');
            });
        }
    }
};
