<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Пилот перехода на Vite + Tailwind: маршруты pilot.* рисуются шаблонами tw.*
    private function view(string $name): string
    {
        return (request()->routeIs('pilot.*') ? 'tw.' : 'public.') . $name;
    }

    public function index()
    {
        $categories = Category::withCount('posts')->get();

        return view($this->view('categories.index'), compact('categories'));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(15);

        return view($this->view('categories.show'), compact('category', 'posts'));
    }
}
