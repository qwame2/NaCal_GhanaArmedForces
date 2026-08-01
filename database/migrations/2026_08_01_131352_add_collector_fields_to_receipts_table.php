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
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'collector_staff_id')) {
                $table->string('collector_staff_id')->nullable()->after('collector_location');
            }
            // Make collector_contact and collector_location nullable in case they weren't before
            // (safe to call even if they already exist)
            $table->string('collector_contact')->nullable()->change();
            $table->string('collector_location')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (Schema::hasColumn('receipts', 'collector_staff_id')) {
                $table->dropColumn('collector_staff_id');
            }
        });
    }
};
