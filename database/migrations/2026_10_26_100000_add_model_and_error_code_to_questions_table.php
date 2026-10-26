<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Марка и модель, код ошибки: по ним ищут, и по модели вопрос связывается с каталогом прошивок
            $table->string('model', 120)->nullable()->after('appliance_id');
            $table->string('error_code', 60)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['model', 'error_code']);
        });
    }
};
