<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // Пилот перехода на Vite + Tailwind: маршруты pilot.* рисуются шаблонами tw.*
    private function view(string $name): string
    {
        return (request()->routeIs('pilot.*') ? 'tw.' : 'public.') . $name;
    }

    public function index()
    {
        $tags = Tag::withCount('posts')->get();

        return view($this->view('tags.index'), compact('tags'));
    }

    public function show($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(10);

        return view($this->view('tags.show'), compact('tag', 'posts'));
    }
}
