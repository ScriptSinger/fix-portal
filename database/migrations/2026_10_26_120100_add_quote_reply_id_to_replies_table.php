<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            // ответ на конкретный ответ; пусто, если отвечали на сам комментарий
            $table->unsignedBigInteger('quote_reply_id')->nullable()->after('comment_id');
        });
    }

    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropColumn('quote_reply_id');
        });
    }
};
