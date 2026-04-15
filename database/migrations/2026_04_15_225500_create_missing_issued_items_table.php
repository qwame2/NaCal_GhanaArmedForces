<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('issued_items')) {
            Schema::create('issued_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issuance_id')->constrained('issuances')->onDelete('cascade');
                $table->string('description');
                $table->string('ledge_category');
                $table->integer('quantity');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_items');
    }
};
