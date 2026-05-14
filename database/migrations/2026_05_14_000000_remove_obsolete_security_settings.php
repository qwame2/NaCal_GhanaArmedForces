<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('settings')
            ->whereIn('key', ['strict_audit_logging', 'enable_strict_audit_logging', 'approval_timeout_minutes'])
            ->delete();
    }

    public function down()
    {
        // Re-inserting with defaults if needed
        DB::table('settings')->insert([
            [
                'group' => 'security',
                'key' => 'enable_strict_audit_logging',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable enhanced audit trails for all administrative actions.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'security',
                'key' => 'approval_timeout_minutes',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Time window for administrative approval before a request expires.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
};
