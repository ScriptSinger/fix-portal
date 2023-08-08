<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(2);;
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);
        Category::create($request->all());
        // return redirect()->route('categories.index')->session()->flash('success', 'Задание добавлено');
        return redirect()->route('categories.index')->with('success', 'Категория добавлена');
    }

    public function edit(string $id)
    {
        $category = Category::find($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required'
        ]);
        $category = Category::find($id);
        // $category->slug = null; // обновление slug
        $category->update($request->all());
        return redirect()->route('categories.index')->with('success', 'Категория отредактирована');
    }

    public function destroy(string $id)
    {
        // Category::destroy($id);
        $category = Category::find($id); //Soft delete
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Задание удалено');
    }
}
