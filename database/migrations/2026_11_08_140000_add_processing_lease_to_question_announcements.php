<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_announcements', function (Blueprint $table): void {
            $table->uuid('processing_token')->nullable();
            $table->timestamp('processing_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('question_announcements', fn (Blueprint $table) => $table->dropColumn(['processing_token', 'processing_until']));
    }
};
