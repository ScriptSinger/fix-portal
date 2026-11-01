<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_widgets', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('block_id')->nullable();
            $table->string('url')->nullable();
            $table->unsignedTinyInteger('display_limit')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('site_widgets');
    }
};
