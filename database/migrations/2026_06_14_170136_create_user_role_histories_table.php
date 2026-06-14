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
        Schema::create('user_role_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // 'created', 'role_changed', 'permissions_changed', 'status_changed'
            $table->string('old_role')->nullable();
            $table->string('new_role')->nullable();
            $table->boolean('old_is_admin')->nullable();
            $table->boolean('new_is_admin')->nullable();
            $table->json('old_permissions')->nullable();
            $table->json('new_permissions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role_histories');
    }
};
