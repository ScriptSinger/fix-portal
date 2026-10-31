<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('replies', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('comment_id')
                ->references('id')
                ->on('comments')
                ->cascadeOnDelete();

            $table->foreign('quote_reply_id')
                ->references('id')
                ->on('replies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropForeign(['quote_reply_id']);
            $table->dropForeign(['comment_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
