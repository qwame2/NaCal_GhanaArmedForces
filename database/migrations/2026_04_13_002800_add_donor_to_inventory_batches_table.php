<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->string('donor_name')->nullable()->after('supplier_name');
            $table->string('acquisition_type')->default('Supplier')->after('donor_name');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn(['donor_name', 'acquisition_type']);
        });
    }
};
