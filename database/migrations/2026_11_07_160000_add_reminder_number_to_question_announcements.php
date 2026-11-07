<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_announcements', function (Blueprint $table): void {
            $table->unsignedTinyInteger('reminder_number')->default(1)->after('kind');
            $table->unique(['question_id', 'channel', 'kind', 'reminder_number'], 'question_announcements_question_channel_kind_number_unique');
        });

        Schema::table('question_announcements', function (Blueprint $table): void {
            $table->dropUnique(['question_id', 'channel', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::table('question_announcements', function (Blueprint $table): void {
            $table->unique(['question_id', 'channel', 'kind']);
            $table->dropUnique('question_announcements_question_channel_kind_number_unique');
            $table->dropColumn('reminder_number');
        });
    }
};
