<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        foreach ($categories as $category) {
            $category->created_at_diff = Carbon::parse($category->created_at)->format('d-m-Y H:i:s');
        }

        return response()->json($categories);
    }
}
