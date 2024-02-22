<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\LikeService;

class LikeController extends Controller
{

    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function like($type, $id)
    {
        $model = "App\\Models\\" . ucfirst($type);
        $instance = $model::findOrFail($id);
        $user = auth()->user();
        $this->likeService->toggleLike($instance, $user);
        $counter = $this->likeService->counter($instance);
        return response()->json($counter);
    }

    public function dislike($type, $id)
    {
        $instance = ("App\\Models\\" . ucfirst($type))::findOrFail($id);
        $user = auth()->user();
        $this->likeService->toggleDisLike($instance, $user);
        $counter = $this->likeService->counter($instance);
        return response()->json($counter);
    }
}
