<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sras', function (Blueprint $table) {
            $table->id();
            $table->string('sra_number')->unique();
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');

            // Header info
            $table->string('dept')->nullable();
            $table->string('station')->nullable();
            $table->string('region')->nullable();
            $table->date('date_of_delivery');

            // Supplier / delivery info
            $table->string('supplier_name');
            $table->string('vehicle_number')->nullable();
            $table->string('ae_number')->nullable();   // A&E No.
            $table->string('lpo_number')->nullable();  // LPO No.
            $table->string('supplier_address')->nullable();

            // Full / Partial delivery
            $table->enum('delivery_type', ['full', 'partial'])->default('full');
            $table->text('previous_sra_nos')->nullable(); // If partial

            // Main content
            $table->text('details');  // Details of Order/Service

            // Admin (Head of Admin) approval
            $table->enum('admin_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->string('admin_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();
            $table->text('admin_notes')->nullable();

            // Stores (Head of Stores) final approval
            $table->enum('stores_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->string('stores_approved_by')->nullable();
            $table->timestamp('stores_approved_at')->nullable();
            $table->text('stores_notes')->nullable();

            // Overall status
            $table->enum('status', ['pending', 'admin_approved', 'approved', 'declined'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sras');
    }
};
