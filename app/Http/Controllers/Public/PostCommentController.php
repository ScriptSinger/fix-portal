<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use App\Models\PostComment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{

    public function store(Request $request, string $id)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        $data['post_id'] = $id;
        PostComment::create($data);
        return redirect()->back();
    }
}
