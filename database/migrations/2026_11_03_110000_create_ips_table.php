<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ips')) {
            return;
        }

        Schema::create('ips', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ips');
    }
};
