<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{


    public function index()
    {
        $categories = Category::withCount('posts')->get();

        return view('tw.categories.index', compact('categories'));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->with('category', 'thumbnail', 'administrator')->orderBy('id', 'desc')->paginate(15);

        return view('tw.categories.show', compact('category', 'posts'));
    }
}
