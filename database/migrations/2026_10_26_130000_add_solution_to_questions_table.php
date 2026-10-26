<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // вопрос решён; если автор выбрал ответ, он хранится как comment или reply
            $table->timestamp('solved_at')->nullable();
            $table->string('solution_type', 16)->nullable();
            $table->unsignedBigInteger('solution_id')->nullable();
            $table->index('solved_at');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['solved_at']);
            $table->dropColumn(['solved_at', 'solution_type', 'solution_id']);
        });
    }
};
