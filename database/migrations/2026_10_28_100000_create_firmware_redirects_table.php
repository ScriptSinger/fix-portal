<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmware_redirects', function (Blueprint $table) {
            $table->unsignedBigInteger('old_id')->primary();
            $table->unsignedBigInteger('new_id')->index();
        });

        // Слитые дубли раньше хранились «в корзине» с merged_into: переносим их адреса, пока записи ещё есть
        if (Schema::hasColumn('firmware', 'merged_into') && Schema::hasColumn('firmware', 'deleted_at')) {
            DB::statement('insert into firmware_redirects (old_id, new_id) select id, merged_into from firmware where deleted_at is not null and merged_into is not null and id <> merged_into');

            // Цепочки (A слит в B, B позже слит в C) схлопываем: старый адрес ведёт сразу на последнюю запись
            for ($i = 0; $i < 10; $i++) {
                $moved = DB::update('update firmware_redirects r join firmware_redirects r2 on r.new_id = r2.old_id set r.new_id = r2.new_id where r.old_id <> r2.new_id');

                if ($moved === 0) {
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('firmware_redirects');
    }
};
