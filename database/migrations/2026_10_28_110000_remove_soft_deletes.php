<?php

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Корзина убрана: записи, удалённые «мягко», стираются окончательно вместе с тем, что от них зависело
 * (комментарии, ответы, реакции, привязки соцсетей, связи статей с тегами), а колонка deleted_at удаляется.
 * Необратимо: перед выкладкой нужна свежая копия базы (db:backup).
 */
return new class extends Migration
{
    /** Таблицы моделей, у которых был трейт SoftDeletes. */
    private const TABLES = [
        'replies', 'comments', 'questions', 'posts', 'firmware', 'users',
        'tags', 'categories', 'appliances', 'blockers', 'blocked_user_agents',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $userIds = Schema::hasColumn('users', 'deleted_at') ? $this->trashed('users')->pluck('id') : collect();

            $removed = [];
            foreach (self::TABLES as $table) {
                $removed[$table] = Schema::hasColumn($table, 'deleted_at') ? $this->trashed($table)->delete() : 0;
            }

            // Привязки и данные стёртых пользователей: иначе их соцсеть осталась бы занятой «пустой» связью
            foreach (array_chunk($userIds->all(), 500) as $ids) {
                foreach (['social_accounts', 'avatars', 'reactions', 'likes', 'dislikes'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                        DB::table($table)->whereIn('user_id', $ids)->delete();
                    }
                }
                DB::table('notifications')->where('notifiable_type', 'App\\Models\\User')->whereIn('notifiable_id', $ids)->delete();
            }

            // Осиротевшее: комментарии к стёртым вопросам, статьям и прошивкам, ответы и реакции к стёртым комментариям
            foreach ([Question::class => 'questions', Post::class => 'posts', Firmware::class => 'firmware'] as $class => $table) {
                DB::table('comments')->where('commentable_type', $class)
                    ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from($table)->whereColumn("{$table}.id", 'comments.commentable_id'))
                    ->delete();
            }
            DB::table('replies')->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('comments')->whereColumn('comments.id', 'replies.comment_id'))->delete();

            foreach ([Comment::class => 'comments', Reply::class => 'replies'] as $class => $table) {
                DB::table('reactions')->where('reactable_type', $class)
                    ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from($table)->whereColumn("{$table}.id", 'reactions.reactable_id'))
                    ->delete();
            }

            DB::table('post_tag')->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('posts')->whereColumn('posts.id', 'post_tag.post_id'))->delete();
            DB::table('post_tag')->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('tags')->whereColumn('tags.id', 'post_tag.tag_id'))->delete();

            Log::info('Корзина убрана, стёрто записей: ' . json_encode($removed));
        });

        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }

        // Адреса слитых прошивок живут в firmware_redirects
        if (Schema::hasColumn('firmware', 'merged_into')) {
            Schema::table('firmware', fn (Blueprint $t) => $t->dropColumn('merged_into'));
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }

        if (! Schema::hasColumn('firmware', 'merged_into')) {
            Schema::table('firmware', fn (Blueprint $t) => $t->unsignedBigInteger('merged_into')->nullable()->index());
        }
    }

    private function trashed(string $table): \Illuminate\Database\Query\Builder
    {
        return DB::table($table)->whereNotNull('deleted_at');
    }
};
