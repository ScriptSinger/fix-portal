<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->index(['created_at', 'solved_at'], 'questions_dashboard_metrics_index');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->index('created_at', 'posts_dashboard_metrics_created_at_index');
        });

        Schema::table('firmware', function (Blueprint $table): void {
            $table->index('created_at', 'firmware_dashboard_metrics_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex('questions_dashboard_metrics_index');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_dashboard_metrics_created_at_index');
        });

        Schema::table('firmware', function (Blueprint $table): void {
            $table->dropIndex('firmware_dashboard_metrics_created_at_index');
        });
    }
};
