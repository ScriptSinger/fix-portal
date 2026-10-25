<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            // По хешу ищем дубли (canonical, проверка при загрузке)
            $table->index('crc32', 'firmware_crc32_index');
        });
    }

    public function down(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropIndex('firmware_crc32_index');
        });
    }
};
