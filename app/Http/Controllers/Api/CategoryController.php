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
        $categories = Category::withTrashed()->get();
        return response()->json($categories);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return  response()->json(['message' => 'Категория успешно удалена'], 200);;
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->find($id);
        if ($category) {
            $category->restore();
            return response()->json(['message' => 'Категория успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'Категория не найдена'], 404);
        }
    }
}
