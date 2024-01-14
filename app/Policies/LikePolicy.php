<?php

namespace App\Policies;

use App\Models\User;

class LikePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function like(User $user, string $type)
    {


        $allowedModels = [
            'Post',
            'Question',
            'Firmware'
        ];

        return in_array(ucfirst($type), $allowedModels);
    }

    public function dislike(User $user, string $type)
    {
        $allowedModels = [
            'Post',
            'Question',
            'Firmware'
        ];

        return in_array(ucfirst($type), $allowedModels);
    }
}
