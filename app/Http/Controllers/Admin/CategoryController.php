<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index');
    }

    public function create()
    {
        return view('admin.categories.create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required'
        ]);
        Category::create($data);
        // return redirect()->route('categories.index')->session()->flash('success', 'Категория добавлена');
        return redirect()->route('categories.index')->with('success', 'Категория добавлена');
    }

    public function edit(string $id)
    {
        $category = Category::find($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required'
        ]);
        $category = Category::find($id);
        // $category->slug = null; // обновление slug
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Категория отредактирована');
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Категория не найдена');
        }

        if ($category->posts->count() > 0) {
            return redirect()->back()->with('error', 'Ошибка! Нельзя удалить категорию, у которой есть связанные записи.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Категория успешно удалена');
    }
}
