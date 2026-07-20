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
        if (Schema::hasTable('inventory_items') && !Schema::hasColumn('inventory_items', 'store_location')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->string('store_location')->default('Store A')->after('remarks');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items', 'store_location')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropColumn('store_location');
            });
        }
    }
};
