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
        Schema::create('firmware', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('path_id'); // краткая запись
            $table->string('title')->nullable();
            $table->integer('size')->nullable();
            $table->date('date')->nullable();
            $table->string('extension')->nullable();
            $table->string('platform')->nullable();
            $table->string('crc32')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmware');
    }
};
