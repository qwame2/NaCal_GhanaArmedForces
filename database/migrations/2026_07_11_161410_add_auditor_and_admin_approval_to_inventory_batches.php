<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->string('auditor_status')->default('pending');
            $table->unsignedBigInteger('auditor_approved_by')->nullable();
            $table->timestamp('auditor_approved_at')->nullable();
            
            $table->string('admin_status')->default('pending');
            $table->unsignedBigInteger('admin_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();

            $table->unsignedBigInteger('stores_approved_by')->nullable();
            $table->timestamp('stores_approved_at')->nullable();

            $table->foreign('auditor_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('stores_approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Backfill existing approved batches
        $firstAuditor = DB::table('users')->where('role', 'Auditor')->first();
        $firstAdmin = DB::table('users')->where('role', 'Main Admin')->first();

        $batches = DB::table('inventory_batches')->get();
        foreach ($batches as $batch) {
            if ($batch->approval_status === 'approved') {
                DB::table('inventory_batches')->where('id', $batch->id)->update([
                    'auditor_status' => 'approved',
                    'auditor_approved_by' => $firstAuditor ? $firstAuditor->id : null,
                    'auditor_approved_at' => $batch->approved_at ?: ($batch->created_at ?: now()),
                    'admin_status' => 'approved',
                    'admin_approved_by' => $batch->approved_by ?: ($firstAdmin ? $firstAdmin->id : null),
                    'admin_approved_at' => $batch->approved_at ?: ($batch->created_at ?: now()),
                    'stores_approved_by' => $batch->approved_by,
                    'stores_approved_at' => $batch->approved_at ?: ($batch->created_at ?: now()),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropForeign(['auditor_approved_by']);
            $table->dropForeign(['admin_approved_by']);
            $table->dropForeign(['stores_approved_by']);
            
            $table->dropColumn([
                'auditor_status',
                'auditor_approved_by',
                'auditor_approved_at',
                'admin_status',
                'admin_approved_by',
                'admin_approved_at',
                'stores_approved_by',
                'stores_approved_at'
            ]);
        });
    }
};
