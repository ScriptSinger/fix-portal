<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function showTagArticles($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->orderBy('id', 'desc')->paginate(2);
        return view('public.tags.show', compact('tag', 'posts'));
    }
}
