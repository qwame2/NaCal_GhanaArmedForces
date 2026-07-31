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
        if (Schema::hasTable('inventory_batches') && !Schema::hasColumn('inventory_batches', 'previous_sra_nos')) {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->text('previous_sra_nos')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_batches') && Schema::hasColumn('inventory_batches', 'previous_sra_nos')) {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->dropColumn('previous_sra_nos');
            });
        }
    }
};
