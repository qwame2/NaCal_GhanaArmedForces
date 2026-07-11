<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_sras', function (Blueprint $table) {
            // Auditor approval columns
            $table->enum('auditor_status', ['pending', 'approved', 'declined'])->default('pending')->after('admin_notes');
            $table->string('auditor_approved_by')->nullable()->after('auditor_status');
            $table->timestamp('auditor_approved_at')->nullable()->after('auditor_approved_by');
            $table->text('auditor_notes')->nullable()->after('auditor_approved_at');
        });

        // Expand the status enum to include 'auditor_pending'
        DB::statement("ALTER TABLE service_sras MODIFY COLUMN status ENUM('pending','auditor_pending','admin_approved','approved','declined') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('service_sras', function (Blueprint $table) {
            $table->dropColumn(['auditor_status', 'auditor_approved_by', 'auditor_approved_at', 'auditor_notes']);
        });

        DB::statement("ALTER TABLE service_sras MODIFY COLUMN status ENUM('pending','admin_approved','approved','declined') DEFAULT 'pending'");
    }
};
