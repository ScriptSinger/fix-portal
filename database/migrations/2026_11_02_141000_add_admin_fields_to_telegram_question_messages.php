<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->unsignedInteger('appliance_id')->nullable()->after('question_id');
            $table->index('appliance_id');
        });

        Schema::table('telegram_moderation_actions', function (Blueprint $table): void {
            $table->unsignedBigInteger('moderator_telegram_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_question_messages', function (Blueprint $table): void {
            $table->dropIndex(['appliance_id']);
            $table->dropColumn('appliance_id');
        });

        Schema::table('telegram_moderation_actions', function (Blueprint $table): void {
            $table->unsignedBigInteger('moderator_telegram_id')->nullable(false)->change();
        });
    }
};
