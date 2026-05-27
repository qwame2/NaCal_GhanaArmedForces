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
        if (!Schema::hasColumn('store_requisitions', 'alternative_status')) {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->string('alternative_status')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('store_requisitions', 'alternative_status')) {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->dropColumn('alternative_status');
            });
        }
    }
};
