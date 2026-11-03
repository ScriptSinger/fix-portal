<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('telegram_webhook_updates', 'attempts')) {
            Schema::table('telegram_webhook_updates', function (Blueprint $table): void {
                $table->unsignedSmallInteger('attempts')->default(0)->after('processed_at');
                $table->timestamp('failed_at')->nullable()->after('attempts');
                $table->text('processing_error')->nullable()->after('failed_at');
                $table->index(['processed_at', 'received_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('telegram_webhook_updates', 'attempts')) {
            Schema::table('telegram_webhook_updates', function (Blueprint $table): void {
                $table->dropIndex(['processed_at', 'received_at']);
                $table->dropColumn(['attempts', 'failed_at', 'processing_error']);
            });
        }
    }
};
