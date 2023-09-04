<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{

    public function index()
    {
        $tags = Tag::paginate(20);
        return view('admin/tags/index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);
        Tag::create($request->all());
        return redirect()->route('tags.index')->with('success', 'Метка добавлена');
    }

    public function edit(string $id)
    {
        $tag = Tag::find($id);
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required'
        ]);
        $tag = Tag::find($id);
        $tag->update($request->all());
        return redirect()->route('tags.index')->with('success', 'Метка отредактирована');
    }

    public function destroy(string $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return redirect()->route('tags.index')->with('error', 'Метка не найдена');
        }

        if ($tag->posts->count() > 0) {
            return redirect()->route('tags.index')->with('error', 'Ошибка! Нельзя удалить метку, у которой есть связанные записи.');
        }

        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Метка успешно удалена');
    }
}
