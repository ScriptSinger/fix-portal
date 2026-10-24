<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * В базе остался внешний ключ firmware.path_id -> paths.id на таблицу, которой нет ни в одной миграции
 * (след старого импорта). Из-за него нельзя добавить ни одной новой прошивки: вставка падает с ошибкой 1452.
 */
return new class () extends Migration {
    public function up(): void
    {
        $hasForeignKey = DB::selectOne(
            "select count(*) as c from information_schema.table_constraints
             where table_schema = database() and table_name = 'firmware'
               and constraint_name = 'firmware_path_id_foreign' and constraint_type = 'FOREIGN KEY'"
        )->c > 0;

        Schema::table('firmware', function (Blueprint $table) use ($hasForeignKey) {
            if ($hasForeignKey) {
                $table->dropForeign('firmware_path_id_foreign');
            }

            $table->unsignedBigInteger('path_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Внешний ключ на несуществующую таблицу не восстанавливаем; path_id остаётся необязательным
    }
};
