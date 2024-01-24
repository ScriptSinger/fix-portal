<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function comments($id)
    {
        $user = User::find(1);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $comments = $user->comments;
        foreach ($comments as $comment) {
            $comment->entity = $this->loadEntity($comment->commentable_type, $comment->commentable_id);
        }
        return response()->json($comments);
    }


    private function loadEntity($type, $id)
    {
        switch ($type) {
            case 'App\\Models\\Post':
                return Post::findOrFail($id);
            case 'App\\Model\\Question':
                return Question::findOrFail($id);
            case 'App\\Model\\Firmware':
                return Firmware::findOrFail($id);
            default:
                return null;
        }
    }
}
