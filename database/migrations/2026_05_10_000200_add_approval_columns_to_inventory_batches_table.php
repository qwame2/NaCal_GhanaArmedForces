<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->string('approval_status')->default('approved'); // Default to approved for legacy/admin
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
        
        // Update existing batches to be approved by the first admin if exists
        $admin = DB::table('users')->where('is_admin', true)->first();
        if ($admin) {
            DB::table('inventory_batches')->update([
                'approval_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now()
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at']);
        });
    }
};
