<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use App\Services\Reputation;

class UserProfileController extends Controller
{
    public function show(User $user): UserProfileResource
    {
        $user->load('avatar')->loadCount('questions');

        $commentIds = Comment::query()
            ->where('user_id', $user->id)
            ->select('id');
        $replyIds = Reply::query()
            ->where('user_id', $user->id)
            ->select('id');

        $user->setAttribute('answers_count', Comment::where('user_id', $user->id)->count()
            + Reply::where('user_id', $user->id)->count());
        $user->setAttribute('solutions_count', Question::query()
            ->where(function ($query) use ($commentIds, $replyIds): void {
                $query
                    ->where(fn ($query) => $query
                        ->where('solution_type', 'comment')
                        ->whereIn('solution_id', $commentIds))
                    ->orWhere(fn ($query) => $query
                        ->where('solution_type', 'reply')
                        ->whereIn('solution_id', $replyIds));
            })
            ->count());
        $reputation = Reputation::countFor($user->id);
        $user->setAttribute('reputation_rank', Reputation::rank($reputation));
        $user->setAttribute('reputation_level', Reputation::rankColor($reputation));

        return new UserProfileResource($user);
    }

    public function redirectByUsername(string $username)
    {
        $user = User::query()->where('username', $username)->firstOrFail();

        return redirect()->route('users.profile', ['user' => $user->id], 301);
    }
}
