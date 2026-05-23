<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('store_requisitions', 'usage_type')) {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->string('usage_type')->default('permanent'); // permanent or temporary
            });
        }
    }

    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->dropColumn('usage_type');
        });
    }
};
