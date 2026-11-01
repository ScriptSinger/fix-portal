<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount(['posts' => fn ($query) => $query->published()])->get();

        return view('public.tags.index', compact('tags'));
    }

    public function show($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->published()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(10);

        return view('public.tags.show', compact('tag', 'posts'));
    }
}
