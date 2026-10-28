<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // письма о том, что человеку ответили или его ответ отмечен решением; выключаются в профиле и по ссылке в письме
            $table->boolean('email_notifications')->default(true);
            // секрет в ссылке «отключить письма»: работает без входа; создаётся при первом письме
            $table->string('unsubscribe_token', 64)->nullable()->unique();
        });

        Schema::table('notifications', function (Blueprint $table) {
            // уведомление уже ушло письмом: повторно в письма не попадает
            $table->timestamp('emailed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropColumn('emailed_at'));

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['unsubscribe_token']);
            $table->dropColumn(['email_notifications', 'unsubscribe_token']);
        });
    }
};
