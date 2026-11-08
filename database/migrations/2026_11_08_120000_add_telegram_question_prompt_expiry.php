<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->unsignedBigInteger('confirmation_prompt_message_id')->nullable();
            $table->timestamp('confirmation_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->dropColumn(['confirmation_prompt_message_id', 'confirmation_expires_at']);
        });
    }
};
