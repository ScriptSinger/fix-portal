<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('telegram_question_messages', 'moderation_chat_id')) {
            Schema::table('telegram_question_messages', function (Blueprint $table): void {
                $table->bigInteger('moderation_chat_id')->nullable()->after('question_id');
            });
        }

        if (! Schema::hasColumn('telegram_question_messages', 'moderation_message_id')) {
            Schema::table('telegram_question_messages', function (Blueprint $table): void {
                $table->unsignedBigInteger('moderation_message_id')->nullable()->after('moderation_chat_id');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('telegram_question_messages', 'moderation_message_id')
                ? 'moderation_message_id'
                : null,
            Schema::hasColumn('telegram_question_messages', 'moderation_chat_id')
                ? 'moderation_chat_id'
                : null,
        ]));

        if ($columns !== []) {
            Schema::table('telegram_question_messages', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
