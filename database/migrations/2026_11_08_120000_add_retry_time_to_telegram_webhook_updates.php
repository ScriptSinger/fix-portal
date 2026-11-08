<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_webhook_updates', function (Blueprint $table): void {
            $table->timestamp('next_attempt_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_webhook_updates', function (Blueprint $table): void {
            $table->dropColumn('next_attempt_at');
        });
    }
};
