<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            // Запись-дубль слита в другую: адрес отвечает 301 на неё
            $table->unsignedBigInteger('merged_into')->nullable()->after('deleted_at')->index();
            // Другие названия того же файла (по одному в строке), по ним работает поиск
            $table->text('aliases')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropColumn(['merged_into', 'aliases']);
        });
    }
};
