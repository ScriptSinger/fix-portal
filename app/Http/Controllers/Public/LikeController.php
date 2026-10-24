<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Database\Eloquent\Model;
use App\Services\LikeService;

class LikeController extends Controller
{

    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    /**
     * Типы, которые можно лайкать. Класс модели нельзя собирать из URL.
     */
    private const LIKEABLE = [
        'comment' => Comment::class,
        'reply' => Reply::class,
    ];

    private function resolve(string $type, int|string $id): Model
    {
        $class = self::LIKEABLE[$type] ?? abort(404);

        return $class::findOrFail($id);
    }

    public function like($type, $id)
    {
        $instance = $this->resolve($type, $id);
        $user = auth()->user();
        $this->likeService->toggleLike($instance, $user);
        $counter = $this->likeService->counter($instance);
        return response()->json($counter);
    }

    public function dislike($type, $id)
    {
        $instance = $this->resolve($type, $id);
        $user = auth()->user();
        $this->likeService->toggleDisLike($instance, $user);
        $counter = $this->likeService->counter($instance);
        return response()->json($counter);
    }
}
