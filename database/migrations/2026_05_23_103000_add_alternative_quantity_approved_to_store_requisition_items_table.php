<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('store_requisition_items', 'alternative_quantity_approved')) {
            Schema::table('store_requisition_items', function (Blueprint $table) {
                $table->decimal('alternative_quantity_approved', 15, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('store_requisition_items', 'alternative_quantity_approved')) {
            Schema::table('store_requisition_items', function (Blueprint $table) {
                $table->dropColumn('alternative_quantity_approved');
            });
        }
    }
};
