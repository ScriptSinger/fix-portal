<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('customizations');
    }

    public function down(): void
    {
        Schema::create('customizations', function (Blueprint $table): void {
            $table->id();
            $table->string('favicon')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('copyright')->nullable();
            $table->timestamps();
        });
    }
};
