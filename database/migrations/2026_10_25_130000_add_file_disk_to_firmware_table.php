<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            // null = файл лежит на локальном диске public (firmwares/{file_path}),
            // иначе имя диска из config/filesystems.php, а file_path это полный ключ объекта
            $table->string('file_disk', 32)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropColumn('file_disk');
        });
    }
};
