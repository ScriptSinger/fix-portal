<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reactable_type');
            $table->unsignedBigInteger('reactable_id');
            $table->string('emoji', 16);
            $table->timestamps();
            // одна реакция пользователя на сообщение, как в Telegram
            $table->unique(['user_id', 'reactable_type', 'reactable_id']);
            $table->index(['reactable_type', 'reactable_id']);
        });

        // Старые лайки и дизлайки сообщений становятся реакциями 👍 и 👎; лайк главнее, если стоят оба
        foreach ([['dislikes', 'dislikeable', '👎'], ['likes', 'likeable', '👍']] as [$source, $morph, $emoji]) {
            DB::table($source)->whereNull('deleted_at')->orderBy('id')->each(function ($row) use ($morph, $emoji) {
                DB::table('reactions')->updateOrInsert(
                    ['user_id' => $row->user_id, 'reactable_type' => $row->{$morph . '_type'}, 'reactable_id' => $row->{$morph . '_id'}],
                    ['emoji' => $emoji, 'created_at' => $row->created_at, 'updated_at' => $row->updated_at],
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
