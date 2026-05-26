<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_temp_account')->default(false)->after('must_change_password');
            $table->string('otp_token')->nullable()->after('is_temp_account');
            $table->foreignId('sponsored_by')->nullable()->constrained('users')->nullOnDelete()->after('otp_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sponsored_by']);
            $table->dropColumn(['is_temp_account', 'otp_token', 'sponsored_by']);
        });
    }
};
