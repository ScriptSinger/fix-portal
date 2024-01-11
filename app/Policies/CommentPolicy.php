<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, string $commentableType): bool
    {
        $allowedModels = ['Post', 'Question', 'Firmware'];
        return in_array(ucfirst($commentableType), $allowedModels);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function like(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function dislike(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
