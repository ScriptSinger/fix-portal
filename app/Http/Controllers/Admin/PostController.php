<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(2);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        $tags = Tag::pluck('title', 'id');
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'content' => 'required',
            'category_id' => 'required|integer',
            'thumbnail' => 'nullable|image'
        ]);
        $data = ($request->all());
        $data['thumbnail'] = Post::uploadThumbnail($request);
        $post = Post::create($data);
        $post->tags()->sync($request->tags);

        return redirect()->route('posts.index')->with('success', 'Статья добавлена');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = Post::find($id);
        $categories = Category::pluck('title', 'id');
        $tags = Tag::pluck('title', 'id');
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'content' => 'required',
            'category_id' => 'required|integer',
            'thumbnail' => 'nullable|image'
        ]);
        $post = Post::find($id);
        $data = ($request->all());
        $data['thumbnail'] = Post::uploadThumbnail($request, $post->thumbnail);
        $post->update($data);
        $post->tags()->sync($request->tags);

        return redirect()->route('posts.index')->with('success', 'Изменеия сохранены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $post = Post::find($id);
        $post->tags()->sync([]);
        Storage::disk('public')->delete($post->thumbnail);
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Статья удалена');
    }
}