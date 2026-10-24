<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            // Путь к файлу внутри диска public. Раньше он собирался из title + extension,
            // а файлы при загрузке переименовывались, поэтому часть скачиваний не находила файл.
            $table->string('file_path', 512)->nullable()->after('crc32');
        });
    }

    public function down(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
