<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->string('resolution_status', 16)->default('open')->after('status');
            $table->timestamp('resolved_at')->nullable()->after('resolution_status');
            $table->unsignedBigInteger('solution_telegram_user_id')->nullable()->after('resolved_at');
            $table->unsignedBigInteger('solution_telegram_message_id')->nullable()->after('solution_telegram_user_id');
            $table->index(['resolution_status', 'telegram_chat_id'], 'tqm_resolution_chat_idx');
            $table->index('solution_telegram_user_id', 'tqm_solution_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->dropIndex('tqm_resolution_chat_idx');
            $table->dropIndex('tqm_solution_user_idx');
            $table->dropColumn([
                'resolution_status',
                'resolved_at',
                'solution_telegram_user_id',
                'solution_telegram_message_id',
            ]);
        });
    }
};
