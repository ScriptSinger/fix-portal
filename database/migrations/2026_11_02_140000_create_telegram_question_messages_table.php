<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_question_messages', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('telegram_chat_id');
            $table->unsignedBigInteger('telegram_message_id');
            $table->unsignedBigInteger('telegram_update_id')->unique();
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->text('text')->nullable();
            $table->json('payload');
            $table->string('status', 32)->default('pending_moderation')->index();
            $table->unsignedInteger('question_id')->nullable();
            $table->bigInteger('moderation_chat_id')->nullable();
            $table->unsignedBigInteger('moderation_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['telegram_chat_id', 'telegram_message_id'], 'telegram_question_messages_chat_message_unique');
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_question_messages');
    }
};
