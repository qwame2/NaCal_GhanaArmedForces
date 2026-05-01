<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('notification_acknowledgements');
        Schema::create('notification_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_description');
            $table->string('alert_type'); // 'low_stock' or 'expired'
            $table->timestamp('acknowledged_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'item_description', 'alert_type'], 'notif_ack_user_item_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_acknowledgements');
    }
};
