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
        if (!Schema::hasColumn('users', 'hod_auto_approve_timeout_mins')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('hod_auto_approve_timeout_mins')->default(5)->after('can_approve_requisition');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'hod_auto_approve_timeout_mins')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('hod_auto_approve_timeout_mins');
            });
        }
    }
};
