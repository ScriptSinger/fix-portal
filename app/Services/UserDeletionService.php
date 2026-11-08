<?php

namespace App\Services;

use App\Jobs\CleanupDeletedUserImages;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserDeletionService
{
    public function delete(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->first();
            if (! $locked) {
                return false;
            }
            if (! app(ImageRegistry::class)->ready()) {
                throw new RuntimeException('Image registry must be indexed before deleting users.');
            }
            $queueConnection = config('queue.connections.database.connection') ?: config('database.default');
            if ($queueConnection !== DB::getDefaultConnection()) {
                throw new RuntimeException('The database queue must use the same database connection as user deletion.');
            }
            $questions = Question::query()->where('user_id', $locked->id)->get();
            $questionIds = $questions->modelKeys();
            $commentIds = Comment::query()->where('user_id', $locked->id)
                ->orWhere(fn ($q) => $q->where('commentable_type', (new Question)->getMorphClass())->whereIn('commentable_id', $questionIds))
                ->pluck('id');
            $replyIds = Reply::query()->where('user_id', $locked->id)->orWhereIn('comment_id', $commentIds)->pluck('id');
            $usage = DB::table('imageables')->where(function ($query) use ($questionIds, $commentIds, $replyIds): void {
                $query->where(fn ($q) => $q->where('imageable_type', (new Question)->getMorphClass())->whereIn('imageable_id', $questionIds))
                    ->orWhere(fn ($q) => $q->where('imageable_type', (new Comment)->getMorphClass())->whereIn('imageable_id', $commentIds))
                    ->orWhere(fn ($q) => $q->where('imageable_type', (new Reply)->getMorphClass())->whereIn('imageable_id', $replyIds));
            });
            $imageIds = (clone $usage)->pluck('image_id')->merge($locked->images()->withTrashed()->pluck('id'))->unique()->values()->all();
            $avatar = DB::table('avatars')->where('user_id', $locked->id)->value('uri');
            CleanupDeletedUserImages::dispatch($imageIds, $avatar)->onConnection('database')->beforeCommit();

            $usage->delete();
            DB::table('reactions')->where('user_id', $locked->id)->orWhere(fn ($q) => $q->where('reactable_type', (new Comment)->getMorphClass())->whereIn('reactable_id', $commentIds))
                ->orWhere(fn ($q) => $q->where('reactable_type', (new Reply)->getMorphClass())->whereIn('reactable_id', $replyIds))->delete();
            $questions->each(fn (Question $question) => app(QuestionAnnouncer::class)->retract($question));
            DB::table('comments')->whereIn('id', $commentIds)->delete();
            DB::table('questions')->whereIn('id', $questionIds)->delete();
            DB::table('social_accounts')->where('user_id', $locked->id)->delete();
            DB::table('avatars')->where('user_id', $locked->id)->delete();
            $locked->notifications()->delete();

            return (bool) $locked->deleteWithoutCleanup();
        });
    }
}
