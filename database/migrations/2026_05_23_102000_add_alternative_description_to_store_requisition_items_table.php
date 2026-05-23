<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('store_requisition_items', 'alternative_description')) {
            Schema::table('store_requisition_items', function (Blueprint $table) {
                $table->string('alternative_description')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('store_requisition_items', function (Blueprint $table) {
            $table->dropColumn('alternative_description');
        });
    }
};
