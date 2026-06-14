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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // 'create', 'update', 'delete'
            $table->string('old_description')->nullable();
            $table->string('new_description')->nullable();
            $table->string('old_unit')->nullable();
            $table->string('new_unit')->nullable();
            $table->string('old_qty')->nullable();
            $table->string('new_qty')->nullable();
            $table->string('old_stock_balance')->nullable();
            $table->string('new_stock_balance')->nullable();
            $table->string('old_variance')->nullable();
            $table->string('new_variance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
