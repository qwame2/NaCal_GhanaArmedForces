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
        Schema::table('store_requisitions', function (Blueprint $table) {
            if (!Schema::hasColumn('store_requisitions', 'requires_dg_approval')) {
                $table->boolean('requires_dg_approval')->default(false);
            }
            if (!Schema::hasColumn('store_requisitions', 'dg_status')) {
                $table->string('dg_status')->nullable();
            }
            if (!Schema::hasColumn('store_requisitions', 'dg_approved_by')) {
                $table->string('dg_approved_by')->nullable();
            }
            if (!Schema::hasColumn('store_requisitions', 'dg_approved_at')) {
                $table->timestamp('dg_approved_at')->nullable();
            }
            if (!Schema::hasColumn('store_requisitions', 'dg_decline_reason')) {
                $table->text('dg_decline_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('store_requisitions', 'requires_dg_approval')) {
                $table->dropColumn('requires_dg_approval');
            }
            if (Schema::hasColumn('store_requisitions', 'dg_status')) {
                $table->dropColumn('dg_status');
            }
            if (Schema::hasColumn('store_requisitions', 'dg_approved_by')) {
                $table->dropColumn('dg_approved_by');
            }
            if (Schema::hasColumn('store_requisitions', 'dg_approved_at')) {
                $table->dropColumn('dg_approved_at');
            }
            if (Schema::hasColumn('store_requisitions', 'dg_decline_reason')) {
                $table->dropColumn('dg_decline_reason');
            }
        });
    }
};
