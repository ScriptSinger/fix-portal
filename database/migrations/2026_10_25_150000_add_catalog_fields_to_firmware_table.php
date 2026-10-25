<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            // Поля каталога для поиска, фильтров и страниц-хабов. Заполняются командой firmware:enrich
            $table->string('brand', 64)->nullable()->after('platform')->index();
            $table->string('brand_group', 64)->nullable()->after('brand')->index();
            $table->string('series', 32)->nullable()->after('brand_group')->index();
            $table->string('model', 64)->nullable()->after('series')->index();
            $table->string('platform_norm', 32)->nullable()->after('model')->index();
        });
    }

    public function down(): void
    {
        Schema::table('firmware', function (Blueprint $table) {
            $table->dropColumn(['brand', 'brand_group', 'series', 'model', 'platform_norm']);
        });
    }
};
