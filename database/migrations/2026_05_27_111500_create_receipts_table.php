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
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requisition_id')->unique();
                $table->string('receipt_number')->unique();
                $table->string('collector_name');
                $table->string('collector_contact');
                $table->string('collector_location');
                $table->timestamp('collected_at');
                $table->unsignedBigInteger('issued_by');
                $table->string('approved_by_dept_head')->nullable();
                $table->string('approved_by_stores_head')->nullable();
                $table->text('items_json');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
