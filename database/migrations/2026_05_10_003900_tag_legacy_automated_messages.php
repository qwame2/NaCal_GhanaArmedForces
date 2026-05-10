<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tag legacy automated messages to clean up existing chat history
        \App\Models\Message::where('message', 'like', '%Awaiting SRA Approval from Admin%')
            ->orWhere('message', 'like', '%SRA APPROVED & COMMITTED%')
            ->orWhere('message', 'like', '%SRA AUTHORIZATION APPROVED%')
            ->orWhere('message', 'like', '%SRA AUTHORIZATION REJECTED%')
            ->orWhere('message', 'like', '%NEW SRA APPROVAL REQUIRED%')
            ->update(['is_automated' => true]);
    }

    public function down(): void
    {
        // No down migration needed for data tag
    }
};
