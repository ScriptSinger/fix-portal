<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('consumers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You can recreate the "consumers" table if you want to rollback this migration.
        Schema::create('consumers', function (Blueprint $table) {
            // Define the table structure if needed.
            $table->id();
            // Add other columns as needed.
            $table->timestamps();
        });
    }
};
