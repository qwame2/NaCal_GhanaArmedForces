<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add personnel-view to any existing sra-awaiting-msg to prevent them from showing on the Admin's side
        DB::table('messages')
            ->where('message', 'like', "%class='sra-awaiting-msg'%")
            ->where('message', 'not like', "%personnel-view%")
            ->update([
                'message' => DB::raw("REPLACE(message, \"class='sra-awaiting-msg'\", \"class='sra-awaiting-msg personnel-view'\")")
            ]);
    }

    public function down(): void
    {
        DB::table('messages')
            ->where('message', 'like', "%class='sra-awaiting-msg personnel-view'%")
            ->update([
                'message' => DB::raw("REPLACE(message, \"class='sra-awaiting-msg personnel-view'\", \"class='sra-awaiting-msg'\")")
            ]);
    }
};
