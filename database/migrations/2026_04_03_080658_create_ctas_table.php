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
        Schema::create('ctas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('post_id');

            $table->string('target_url', 2048);

            $table->string('city', 64)->nullable();
            $table->string('brand', 64)->nullable();
            $table->string('appliance_type', 64)->nullable();
            $table->string('problem', 64)->nullable();

            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->string('anchor')->nullable();

            $table->string('placement', 32)->default('end');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->index(['post_id', 'placement', 'is_active']);
            $table->index(['city', 'brand', 'appliance_type', 'problem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas');
    }
};
