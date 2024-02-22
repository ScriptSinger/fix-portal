<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Post\TitleFilter;
use App\Models\Post;
use Illuminate\Pipeline\Pipeline;

class PostController extends Controller
{
    public function index()
    {
        $posts = app(Pipeline::class)
            ->send(Post::query())
            ->through([
                TitleFilter::class
            ])
            ->thenReturn()
            ->with('category')
            ->orderBy('id', 'desc')
            ->paginate(2);
        return view('public.posts.index', compact('posts'));
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->views += 1;
        $post->update();
        return view('public.posts.show', compact('post'));
    }
}
