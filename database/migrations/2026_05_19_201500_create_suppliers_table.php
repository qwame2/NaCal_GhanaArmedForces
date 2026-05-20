<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('delivery_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('desc')->nullable();
            $table->timestamps();
        });

        // Migrate existing suppliers from setting
        try {
            $setting = DB::table('settings')->where('key', 'suppliers_registry')->first();
            if ($setting && $setting->value) {
                $registry = json_decode($setting->value, true);
                if (is_array($registry)) {
                    foreach ($registry as $name => $details) {
                        DB::table('suppliers')->insert([
                            'name' => $name,
                            'delivery_person' => $details['delivery_person'] ?? null,
                            'phone' => $details['phone'] ?? null,
                            'email' => $details['email'] ?? null,
                            'address' => $details['address'] ?? null,
                            'desc' => $details['desc'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                // Delete the old setting key to prevent redundant data
                DB::table('settings')->where('key', 'suppliers_registry')->delete();
            }
        } catch (\Exception $e) {
            // Log/Ignore error if table settings does not exist or has issue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
