<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{


    public function showCategoryArticles($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->orderBy('id', 'desc')->paginate(2);
        return view('public.categories.show', compact('category', 'posts'));
    }
}
