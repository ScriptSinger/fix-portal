<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\FileUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        return view('admin.posts.index');
    }

    public function create()
    {
        $categories = Category::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        $tags = Tag::pluck('title', 'id');
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = FileUploader::getInstance()
            ->setData($data)
            ->setSupDir('post')
            ->setSubDir(date('Y-m-d'))
            ->save()
            ->getData();

        $post = Post::create($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        return redirect()->route('admin.posts.index')->with('success', 'Статья добавлена');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::pluck('title', 'id');
        $tags = Tag::pluck('title', 'id');
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $post = Post::find($id);

        $data = FileUploader::getInstance()
            ->setData($data)
            ->setModel($post)
            ->setSupDir('post')
            ->setSubDir(date('Y-m-d'))
            ->removePrev()
            ->save()
            ->getData();

        $post->update($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        return redirect()->route('admin.posts.index')->with('success', 'Изменения сохранены');
    }

    public function destroy(string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return redirect()->route('posts.index')->with('error', 'Статья не найдена');
        }

        // Убедимся, что $post->thumbnail не равно null перед удалением файла
        if ($post->thumbnail !== null) {
            Storage::disk('public')->delete($post->thumbnail);
        }

        $post->tags()->sync([]);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Статья удалена');
    }
}
