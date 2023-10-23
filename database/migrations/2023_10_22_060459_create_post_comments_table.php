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
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id(); // Автоинкрементируемый идентификатор комментария
            $table->text('text'); // Текст комментария
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users'); // Ссылка на таблицу 'users'
            $table->integer('post_id')->unsigned(); // Внешний ключ на пост, к которому относится комментарий
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade'); // Установка внешнего ключа
            $table->timestamps(); // Добавление временных меток created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};
