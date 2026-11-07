<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->timestamp('registered_at')->nullable();
            $table->unsignedBigInteger('telegram_thread_id')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->index(['telegram_chat_id', 'registered_at'], 'tqm_registered_chat_idx');
        });

        Schema::create('telegram_question_publications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('telegram_question_message_id')->unique('tqp_question_unique');
            $table->string('status', 32)->default('pending_moderation')->index();
            $table->unsignedInteger('question_id')->nullable()->index();
            $table->unsignedBigInteger('appliance_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // The ledger deliberately survives moderation queue cleanup.
        Schema::create('telegram_solution_awards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('telegram_question_message_id')->unique('tsa_question_unique');
            $table->bigInteger('telegram_chat_id');
            $table->unsignedBigInteger('telegram_user_id')->index();
            $table->unsignedBigInteger('telegram_message_id');
            $table->unsignedBigInteger('command_message_id')->nullable();
            $table->timestamps();
            $table->unique(['telegram_chat_id', 'telegram_message_id'], 'tsa_answer_unique');
        });

        Schema::create('telegram_question_reminders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('telegram_question_message_id');
            $table->unsignedTinyInteger('number');
            $table->string('status', 16)->default('pending');
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['telegram_question_message_id', 'number'], 'tqr_question_number_unique');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_question_reminders');
        Schema::dropIfExists('telegram_solution_awards');
        Schema::dropIfExists('telegram_question_publications');
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->dropIndex('tqm_registered_chat_idx');
            $table->dropColumn(['registered_at', 'telegram_thread_id', 'archived_at']);
        });
    }
};
