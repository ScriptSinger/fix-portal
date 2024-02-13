<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function index()
    {
        $replies = Reply::all();
        return view('admin.replies.index', compact('replies'));
    }

    public function edit($id)
    {
        $reply = Reply::with('user')->findOrfail($id);
        return view('admin.replies.edit', compact('reply'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'text' => 'required'
        ]);

        $reply = Reply::findOrFail($id);
        $reply->update($data);
        return redirect()->route('admin.replies.index')->with('success', 'Отредактировано');
    }
}
