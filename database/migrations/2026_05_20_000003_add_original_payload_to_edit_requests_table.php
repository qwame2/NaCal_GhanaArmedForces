<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('edit_requests', 'original_payload')) {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->text('original_payload')->nullable()->after('payload')
                    ->comment('JSON: original values before edits were made');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('edit_requests', 'original_payload')) {
            Schema::table('edit_requests', function (Blueprint $table) {
                $table->dropColumn('original_payload');
            });
        }
    }
};
