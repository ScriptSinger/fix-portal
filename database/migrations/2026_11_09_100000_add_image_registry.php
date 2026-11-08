<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->string('disk')->nullable();
            $table->text('path')->nullable();
        });
        Schema::create('imageables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('image_id')->constrained('images')->cascadeOnDelete();
            $table->morphs('imageable');
            $table->string('field', 32);
            $table->unique(['image_id', 'imageable_type', 'imageable_id', 'field'], 'image_usage_unique');
        });
        Schema::create('image_registry_state', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->timestamp('indexed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_registry_state');
        Schema::dropIfExists('imageables');
        Schema::table('images', fn (Blueprint $table) => $table->dropColumn(['disk', 'path']));
    }
};
