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
        // 1. Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'registration_status')) {
                $table->string('registration_status')->default('approved');
            }
            if (!Schema::hasColumn('users', 'service_number')) {
                $table->string('service_number')->nullable();
            }
        });

        // 2. Add columns to store_requisitions table
        Schema::table('store_requisitions', function (Blueprint $table) {
            if (!Schema::hasColumn('store_requisitions', 'origin_approved_by')) {
                $table->string('origin_approved_by')->nullable();
            }
            if (!Schema::hasColumn('store_requisitions', 'stores_approved_by')) {
                $table->string('stores_approved_by')->nullable();
            }
        });

        // 3. Add column to issued_items table
        Schema::table('issued_items', function (Blueprint $table) {
            if (!Schema::hasColumn('issued_items', 'unit')) {
                $table->string('unit')->nullable();
            }
        });

        // 4. Add column to issuances table
        Schema::table('issuances', function (Blueprint $table) {
            if (!Schema::hasColumn('issuances', 'requisition_id')) {
                $table->unsignedBigInteger('requisition_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'registration_status')) {
                $table->dropColumn('registration_status');
            }
            if (Schema::hasColumn('users', 'service_number')) {
                $table->dropColumn('service_number');
            }
        });

        Schema::table('store_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('store_requisitions', 'origin_approved_by')) {
                $table->dropColumn('origin_approved_by');
            }
            if (Schema::hasColumn('store_requisitions', 'stores_approved_by')) {
                $table->dropColumn('stores_approved_by');
            }
        });

        Schema::table('issued_items', function (Blueprint $table) {
            if (Schema::hasColumn('issued_items', 'unit')) {
                $table->dropColumn('unit');
            }
        });

        Schema::table('issuances', function (Blueprint $table) {
            if (Schema::hasColumn('issuances', 'requisition_id')) {
                $table->dropColumn('requisition_id');
            }
        });
    }
};
