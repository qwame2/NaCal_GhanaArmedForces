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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'can_add_inventory')) {
                $table->boolean('can_add_inventory')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'can_operate_logistics')) {
                $table->boolean('can_operate_logistics')->default(true)->after('can_add_inventory');
            }
            if (!Schema::hasColumn('users', 'can_generate_reports')) {
                $table->boolean('can_generate_reports')->default(true)->after('can_operate_logistics');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_add_inventory', 'can_operate_logistics', 'can_generate_reports']);
        });
    }
};
