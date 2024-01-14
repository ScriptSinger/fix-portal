<?php

namespace App\Services;

use App\Models\Dislike;
use App\Models\Like;
use App\Models\User;

class LikeService
{
    public function toggleLike($instance, User $user)
    {
        $existingLike = $instance->likes()->where('user_id', $user->id)->first();
        $existingDislike = $instance->dislikes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $like = new Like();

            $like->user_id = $user->id;
            $instance->likes()->save($like);

            // Если у пользователя уже был дизлайк, убираем дизлайк
            if ($existingDislike) {
                $existingDislike->delete();
            }
        }
    }

    public function toggleDislike($instance, User $user)
    {
        $existingLike = $instance->likes()->where('user_id', $user->id)->first();
        $existingDislike = $instance->dislikes()->where('user_id', $user->id)->first();

        if ($existingDislike) {
            $existingDislike->delete();
        } else {
            $dislike = new Dislike();
            $dislike->user_id = $user->id;
            $instance->dislikes()->save($dislike);

            // Если у пользователя уже был лайк, убираем лайк
            if ($existingLike) {
                $existingLike->delete();
            }
        }
    }



    public function counter($instance)
    {
        $likeCount = $instance->likes()->count();
        $dislikeCount = $instance->dislikes()->count();
        return [
            'likeCount' => $likeCount,
            'dislikeCount' => $dislikeCount,
        ];
    }
}
