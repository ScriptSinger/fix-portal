<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('ads_enabled')->default(false);
            $table->timestamps();
        });

        // Одна строка настроек. Реклама выключена, пока не оформлен договор с РСЯ: админ включит её ползунком
        DB::table('site_settings')->insert(['ads_enabled' => false, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
