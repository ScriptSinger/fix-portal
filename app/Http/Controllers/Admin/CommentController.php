<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::all();
        return view('admin.comments.index', compact('comments'));
    }

    public function edit($id)
    {
        $comment = Comment::with('user')->findOrfail($id);
        return view('admin.comments.edit', compact('comment'));
    }

    public function update(Request $request,  $id)
    {
        $data = $request->validate([
            'text' => 'required'
        ]);

        $comment = Comment::findOrfail($id);
        $comment->update($data);

        return redirect()->route('admin.comments.index')->with('success', 'Комментарий отредактирован');
    }
}
