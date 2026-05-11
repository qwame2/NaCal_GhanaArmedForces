<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed more advanced settings
        DB::table('settings')->insert([
            [
                'key' => 'organization_name',
                'value' => 'NACOC',
                'type' => 'string',
                'group' => 'system',
                'description' => 'The official name of the organization displayed across the platform.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'support_email',
                'value' => 'support@nacoc.gov',
                'type' => 'string',
                'group' => 'system',
                'description' => 'The email address used for system support and automated notifications.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_strict_audit_logging',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enforce strict logging of all read and write operations by personnel.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Maximum allowed failed login attempts before an account is temporarily locked.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'default_pagination_limit',
                'value' => '15',
                'type' => 'integer',
                'group' => 'ui',
                'description' => 'The default number of rows to display in inventory and log tables.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'allow_personnel_registration',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Allow new personnel to register accounts (requires admin approval later).',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'organization_name',
            'support_email',
            'enable_strict_audit_logging',
            'max_login_attempts',
            'default_pagination_limit',
            'allow_personnel_registration'
        ])->delete();
    }
};
