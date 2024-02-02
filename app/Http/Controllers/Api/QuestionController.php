<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::withTrashed()->get();
        return response()->json($questions);
    }

    public function destroy(string $id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return  response()->json(['message' => 'Вопрос успешно удален'], 200);;
    }

    public function restore($id)
    {
        $question = Question::withTrashed()->find($id);
        if ($question) {
            $question->restore();
            return response()->json(['message' => 'Вопрос успешно восстановлен'], 200);
        } else {
            return response()->json(['error' => 'Вопрос не найден'], 404);
        }
    }
}
