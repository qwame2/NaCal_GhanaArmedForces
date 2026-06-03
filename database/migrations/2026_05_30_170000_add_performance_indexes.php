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
        // 1. inventory_items
        try {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->index('description');
                $table->index('category');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 2. inventory_batches
        try {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->index('supplier_status');
                $table->index('ledge_category');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 3. store_requisitions
        try {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->index('department');
                $table->index('status');
                $table->index('alternative_status');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 4. store_requisition_items
        try {
            Schema::table('store_requisition_items', function (Blueprint $table) {
                $table->index('description');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 5. messages
        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->index('sender_id');
                $table->index('receiver_id');
                $table->index('is_automated');
                $table->index('is_archived');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 6. system_logs
        try {
            Schema::table('system_logs', function (Blueprint $table) {
                $table->index('event_type');
                $table->index('action');
                $table->index('is_archived');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }

        // 7. edit_requests
        try {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->index('status');
                $table->index('request_type');
            });
        } catch (\Exception $e) {
            // Index already exists or error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        try {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropIndex(['description']);
                $table->dropIndex(['category']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->dropIndex(['supplier_status']);
                $table->dropIndex(['ledge_category']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->dropIndex(['department']);
                $table->dropIndex(['status']);
                $table->dropIndex(['alternative_status']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('store_requisition_items', function (Blueprint $table) {
                $table->dropIndex(['description']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['sender_id']);
                $table->dropIndex(['receiver_id']);
                $table->dropIndex(['is_automated']);
                $table->dropIndex(['is_archived']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('system_logs', function (Blueprint $table) {
                $table->dropIndex(['event_type']);
                $table->dropIndex(['action']);
                $table->dropIndex(['is_archived']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['request_type']);
            });
        } catch (\Exception $e) {}
    }
};
