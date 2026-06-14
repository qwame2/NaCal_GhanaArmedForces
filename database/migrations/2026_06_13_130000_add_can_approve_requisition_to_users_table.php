<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'can_approve_requisition')) {
                $table->boolean('can_approve_requisition')->default(true)->after('can_make_requisition');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'can_approve_requisition')) {
                $table->dropColumn('can_approve_requisition');
            }
        });
    }
};
