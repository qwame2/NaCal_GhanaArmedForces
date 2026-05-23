<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('store_requisitions', 'decline_reason')) {
            Schema::table('store_requisitions', function (Blueprint $table) {
                $table->text('decline_reason')->nullable()->after('admin_notes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->dropColumn('decline_reason');
        });
    }
};
