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
            ->paginate(10);
        return view('public.posts.index', compact('posts'));
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->views += 1;
        $post->update();

        $postTags = $post->tags;
        // Найдите другие статьи, содержащие хотя бы один из этих тегов
        $relatedPosts = Post::whereHas('tags', function ($query) use ($postTags) {
            $query->whereIn('title', $postTags->pluck('title'));
        })->where('slug', '!=', $slug)
            ->orderBy('views', 'desc') // Сортировка по количеству просмотров в порядке убывания
            ->take(2) // Ограничение количества результатов до 2
            ->with('category') // Загрузка связанной категории
            ->get();

        return view('public.posts.show', compact('post', 'relatedPosts'));
    }
}
