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
        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'delivery_phone')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('delivery_phone')->nullable()->after('delivery_person');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'delivery_phone')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('delivery_phone');
            });
        }
    }
};
