<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\ResolvesPilotView;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ResolvesPilotView;


    public function index()
    {
        $categories = Category::withCount('posts')->get();

        return view($this->pilotView('categories.index'), compact('categories'));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(15);

        return view($this->pilotView('categories.show'), compact('category', 'posts'));
    }
}
