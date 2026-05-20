<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requester_name');
            $table->string('department');
            $table->string('rank_or_title')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('purpose');
            $table->enum('priority', ['low', 'normal', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'approved', 'partially_approved', 'declined'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('store_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained('store_requisitions')->cascadeOnDelete();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('unit')->default('units');
            $table->decimal('quantity_requested', 15, 2)->default(0);
            $table->decimal('quantity_approved', 15, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_requisition_items');
        Schema::dropIfExists('store_requisitions');
    }
};
