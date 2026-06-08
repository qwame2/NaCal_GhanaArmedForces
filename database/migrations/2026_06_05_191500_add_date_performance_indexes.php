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
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->index('entry_date');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('issuances', function (Blueprint $table) {
                $table->index('issuance_date');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('system_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->dropIndex(['entry_date']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('issuances', function (Blueprint $table) {
                $table->dropIndex(['issuance_date']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('system_logs', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        } catch (\Exception $e) {}
    }
};
