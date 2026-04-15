<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issuances', function (Blueprint $table) {
            if (!Schema::hasColumn('issuances', 'issuance_date')) {
                $table->date('issuance_date')->after('id');
            }
            if (!Schema::hasColumn('issuances', 'beneficiary')) {
                $table->string('beneficiary')->after('issuance_date');
            }
            if (!Schema::hasColumn('issuances', 'siv_no')) {
                $table->string('siv_no')->after('beneficiary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('issuances', function (Blueprint $table) {
            $table->dropColumn(['issuance_date', 'beneficiary', 'siv_no']);
        });
    }
};
