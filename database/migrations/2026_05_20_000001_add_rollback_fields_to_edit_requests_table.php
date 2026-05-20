<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('edit_requests', 'rollback_fields')) {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->text('rollback_fields')->nullable()->after('payload')
                    ->comment('JSON: admin-flagged fields and correction notes for rollback');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('edit_requests', 'rollback_fields')) {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->dropColumn('rollback_fields');
            });
        }
    }
};
