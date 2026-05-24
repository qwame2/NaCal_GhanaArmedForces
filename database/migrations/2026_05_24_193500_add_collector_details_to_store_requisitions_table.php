<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->string('collector_name')->nullable();
            $table->string('collector_contact')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->dropColumn(['collector_name', 'collector_contact']);
        });
    }
};
