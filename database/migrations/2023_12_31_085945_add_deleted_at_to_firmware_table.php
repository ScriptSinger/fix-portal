<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->softDeletes(); // Добавляем soft delete столбец
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Отменяем добавление soft delete столбца
        });
    }
};
