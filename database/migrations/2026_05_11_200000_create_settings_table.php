<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // security, inventory, ui, etc.
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        // Seed default settings
        DB::table('settings')->insert([
            [
                'key' => 'low_stock_threshold',
                'value' => '100',
                'type' => 'integer',
                'group' => 'inventory',
                'description' => 'The minimum stock level before triggering a low stock alert.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'approval_timeout_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Number of minutes before an approved security clearance expires.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'system_maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Restrict access to the system for maintenance.',
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
        Schema::dropIfExists('settings');
    }
};
