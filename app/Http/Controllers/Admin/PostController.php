<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ThumbnailService;
use Illuminate\Http\Request;

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
        $post = Post::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
            'category_id' => $data['category_id'],
            'administrator_id' => auth()->guard('admin')->user()->id,
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        $paths = ThumbnailService::getInstance()->store($request->file('thumbnail'));
        $post->thumbnail()->create($paths);
        return redirect()->route('admin.posts.index')->with('success', 'Статья добавлена');
    }

    public function show(int $id)
    {
        //
    }

    public function edit(int $id)
    {
        $post = Post::with('administrator')->findOrFail($id);
        $categories = Category::pluck('title', 'id');
        $tags = Tag::pluck('title', 'id');
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $post = Post::findOrFail($id);
        $paths = [
            $post->thumbnail->original,
            $post->thumbnail->blog,
            $post->thumbnail->small
        ];

        $paths = ThumbnailService::getInstance()
            ->destroy($paths)
            ->store($request->file('thumbnail'));

        $post->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
            'category_id' => $data['category_id'],
            'administrator_id' => auth()->guard('admin')->user()->id,
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        $post->thumbnail()->update($paths);
        return redirect()->route('admin.posts.index')->with('success', 'Изменения сохранены');
    }

    public function destroy(int $id)
    {
        $post = Post::findOrFail($id);
        $paths = [
            $post->thumbnail->original,
            $post->thumbnail->blog,
            $post->thumbnail->small
        ];
        $paths = ThumbnailService::getInstance()
            ->destroy($paths);
        $post->tags()->sync([]);
        $post->forceDelete();
        return redirect()->route('admin.posts.index')->with('success', 'Статья удалена');
    }
}
