<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Dislike;
use App\Models\Like;
use App\Models\User;

class LikeService
{
    public function toggleLike(Comment $comment, User $user)
    {
        $existingLike = $comment->likes()->where('user_id', $user->id)->first();
        $existingDislike = $comment->dislikes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $like = new Like();

            $like->user_id = $user->id;
            $comment->likes()->save($like);

            // Если у пользователя уже был дизлайк, убираем дизлайк
            if ($existingDislike) {
                $existingDislike->delete();
            }
        }
    }

    public function toggleDislike(Comment $comment, User $user)
    {
        $existingLike = $comment->likes()->where('user_id', $user->id)->first();
        $existingDislike = $comment->dislikes()->where('user_id', $user->id)->first();

        if ($existingDislike) {
            $existingDislike->delete();
        } else {
            $dislike = new Dislike();
            $dislike->user_id = $user->id;
            $comment->dislikes()->save($dislike);

            // Если у пользователя уже был лайк, убираем лайк
            if ($existingLike) {
                $existingLike->delete();
            }
        }
    }



    public function counter(Comment $comment)
    {
        $likeCount = $comment->likes()->count();
        $dislikeCount = $comment->dislikes()->count();

        return [
            'likeCount' => $likeCount,
            'dislikeCount' => $dislikeCount,
        ];
    }
}
