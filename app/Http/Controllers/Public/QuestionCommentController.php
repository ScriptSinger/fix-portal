<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\QuestionComment;
use Illuminate\Http\Request;

class QuestionCommentController extends Controller
{
    public function store(Request $request, string $id)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        $data['question_id'] = $id;
        QuestionComment::create($data);
        return redirect()->back();
    }
}
