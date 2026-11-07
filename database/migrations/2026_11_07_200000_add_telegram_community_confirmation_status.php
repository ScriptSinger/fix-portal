<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->string('community_confirmation_status', 16)->nullable();
        });

        DB::table('telegram_question_messages')->whereNotNull('registered_at')->update(['community_confirmation_status' => 'confirmed']);
        DB::table('telegram_question_messages')->whereNull('registered_at')->where('status', 'awaiting_confirmation')->update(['community_confirmation_status' => 'pending']);
        DB::table('telegram_question_messages')->whereNull('registered_at')->where('status', 'cancelled')->update(['community_confirmation_status' => 'declined']);
    }

    public function down(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->dropColumn('community_confirmation_status');
        });
    }
};
