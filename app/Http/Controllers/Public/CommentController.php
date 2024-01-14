<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\FileUploader;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function store(Request $request, $type, $id)
    {
        $this->authorize('create', [Comment::class, $type]);
        $data = $request->validate([
            'text' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Проверка на изображения и ограничение размера до 2 МБ
            'photos' => 'nullable|array|max:4', // Максимальное количество файлов в массиве photos: 4
        ]);
        $commentable = ("App\\Models\\" . ucfirst($type))::findOrFail($id);
        $user_id = auth()->user()->id;
        $data['user_id'] = $user_id;
        $path = date('Y-m-d') . "/" . $user_id;
        $data = FileUploader::getInstance()
            ->setData($data)
            ->setSupDir('comments')
            ->setSubDir($path)
            ->multipleSave()
            ->getData();

        $comment = $commentable->comments()->create($data);
        return redirect()->back();
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
        $comment->replies()->delete();
        return redirect()->back();
    }
}
