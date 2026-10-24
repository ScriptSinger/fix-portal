<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Запросы теперь пишутся в файлы (storage/logs/access-*.log), таблица больше не нужна.
 * Данные заархивированы перед удалением, down() возвращает пустую таблицу той же структуры.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('request_logs');
    }

    public function down(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('ipAddress');
            $table->string('userAgent')->nullable();
            $table->string('locale')->nullable();
            $table->string('referer')->nullable();
            $table->string('methodType');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }
};
