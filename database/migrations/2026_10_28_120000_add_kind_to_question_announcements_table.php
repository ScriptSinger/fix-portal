<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_announcements', function (Blueprint $table) {
            // new: объявление о новом вопросе, reminder: напоминание о вопросе без ответа
            $table->string('kind', 16)->default('new')->after('channel');
        });

        // Новый индекс создаётся первым: внешнему ключу на question_id всегда нужен индекс, начинающийся с него
        Schema::table('question_announcements', function (Blueprint $table) {
            $table->unique(['question_id', 'channel', 'kind']);
        });

        Schema::table('question_announcements', function (Blueprint $table) {
            $table->dropUnique(['question_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::table('question_announcements', function (Blueprint $table) {
            $table->unique(['question_id', 'channel']);
        });

        Schema::table('question_announcements', function (Blueprint $table) {
            $table->dropUnique(['question_id', 'channel', 'kind']);
            $table->dropColumn('kind');
        });
    }
};
