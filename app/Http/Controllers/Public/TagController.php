<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{


    public function index()
    {
        $tags = Tag::withCount('posts')->get();

        return view('tw.tags.index', compact('tags'));
    }

    public function show($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(10);

        return view('tw.tags.show', compact('tag', 'posts'));
    }
}
