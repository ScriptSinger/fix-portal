<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Questions\StatusFilter;
use App\Http\Filters\Questions\TextFilter;

use App\Models\Appliance;
use App\Models\Question;
use App\Http\Requests\Public\SaveQuestionRequest;
use App\Services\QuestionService;

use Illuminate\Pipeline\Pipeline;

class QuestionController extends Controller
{

    public function index()
    {
        $questions = app(Pipeline::class)
            ->send(Question::query())
            ->through([
                TextFilter::class,
                StatusFilter::class,
            ])
            ->thenReturn()
            ->with('appliance', 'user')
            ->orderBy('id', 'desc')
            ->paginate(50);

        $appliances = Appliance::pluck('title', 'id');

        return view('public.questions.index', compact('questions', 'appliances'));
    }

    /** Отдельной страницы нет: форма стоит над списком вопросов, кнопки «Задать вопрос» ведут к ней. */
    public function create()
    {
        return redirect(route('questions.index') . '#ask');
    }

    public function store(SaveQuestionRequest $request, QuestionService $questions)
    {
        $question = $questions->create($request->user(), $request->validated());

        // человек сразу видит свой вопрос и может дополнить его, а не ищет его в общем списке
        return redirect()->route('questions.show', ['question' => $question->slug])->with('success', 'Вопрос опубликован');
    }

    public function show(string $slug)
    {
        $question = Question::where('slug', $slug)->with(['comments.user.avatar', 'comments.reactions', 'comments.replies.user.avatar', 'comments.replies.reactions'])->firstOrFail();
        $question->views += 1;
        $question->update();

        return view('public.questions.show', compact('question'));
    }

    public function edit(string $slug)
    {
        $appliances = Appliance::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        $question = Question::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $question);
        return view('public.questions.edit', compact('question', 'appliances'));
    }

    public function update(SaveQuestionRequest $request, QuestionService $questions, string $slug)
    {
        $question = $questions->update($request->edited() ?? abort(404), $request->user(), $request->validated());

        return redirect()->route('questions.show', ['question' => $question->slug])->with('success', 'Изменения сохранены');
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $this->authorize('update', $question);
        $question->comments()->delete();
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Вопрос удален');
    }
}
