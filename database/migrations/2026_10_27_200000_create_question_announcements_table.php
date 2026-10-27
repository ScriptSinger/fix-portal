<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('question_id');
            $table->string('channel', 16);
            $table->string('status', 16)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            // номер сообщения в Telegram или записи на стене VK: по нему сообщение можно удалить или поправить
            $table->string('external_id', 64)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Один вопрос уходит в один канал один раз, повторный запуск не создаёт дубль
            $table->unique(['question_id', 'channel']);
            $table->index('status');
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_announcements');
    }
};
