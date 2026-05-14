<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false);
        });
        Schema::table('system_logs', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
    }
};
