<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('blocked_at')->nullable()->index();
            $table->timestamp('muted_until')->nullable()->index();
            $table->string('moderation_reason')->nullable();
        });

        Schema::create('telegram_moderation_actions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('moderator_telegram_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 32);
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->index(['moderator_telegram_id', 'created_at'], 'telegram_moderation_moderator_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_moderation_actions');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['blocked_at', 'muted_until', 'moderation_reason']);
        });
    }
};
