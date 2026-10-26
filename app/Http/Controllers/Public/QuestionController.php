<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Questions\TextFilter;

use App\Models\Appliance;
use App\Models\Question;
use App\Support\ContentImages;
use App\Support\MessageBody;
use App\Services\FileUploader;


use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{

    public function index()
    {
        $questions = app(Pipeline::class)
            ->send(Question::query())
            ->through([
                TextFilter::class
            ])
            ->thenReturn()
            ->with('appliance', 'user')
            ->when(request('status') === 'solved', fn ($q) => $q->whereNotNull('solved_at'))
            ->when(request('status') === 'open', fn ($q) => $q->whereNull('solved_at')->where('created_at', '>=', Question::SOLUTION_TRACKING_SINCE))

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

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());
        $data['user_id'] = auth()->user()->id;
        $data['description'] = MessageBody::make($data['description'] ?? null, $data['images'] ?? [], $data['user_id']);
        unset($data['images']);
        Question::create($data);
        return redirect()->route('questions.index')->with('success', 'Вопрос создан');
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

    public function update(Request $request, string $slug)
    {
        $question = Question::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $question);
        $data = $request->validate($this->rules($question->id), $this->messages());
        $data['user_id'] = $request->user()->id;
        $kept = array_column(ContentImages::extract($question->description)[1], 'full');
        $data['description'] = MessageBody::make($data['description'] ?? null, $data['images'] ?? [], $data['user_id'], $kept);
        unset($data['images']);
        $question->update($data);
        return redirect()->route('questions.index')->with('success', 'Изменения сохранены');
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $this->authorize('update', $question);
        $question->comments()->delete();
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Вопрос удален');
    }

    /** Правила формы: заголовок с симптомом, содержательное описание, необязательные модель и код ошибки. */
    private function rules(?int $ignoreId = null): array
    {
        return [
            'title' => ['required', 'string', 'min:12', 'max:150', Rule::unique('questions', 'title')->whereNull('deleted_at')->ignore($ignoreId)],
            'appliance_id' => ['required', 'exists:appliances,id'],
            'model' => ['nullable', 'string', 'max:120'],
            'error_code' => ['nullable', 'string', 'max:60'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000', function (string $attribute, mixed $value, \Closure $fail) {
                if (mb_strlen(trim((string) $value)) < 40 && ! request()->filled('images')) {
                    $fail('Опишите проблему подробнее: когда началось и что уже проверяли (хотя бы 40 знаков) или приложите фото.');
                }
            }],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Напишите, что случилось.',
            'title.min' => 'Заголовок слишком короткий: опишите симптом и прибор (не меньше 12 знаков).',
            'title.max' => 'Заголовок длиннее 150 знаков: сократите, подробности перенесите в описание.',
            'title.unique' => 'Вопрос с таким заголовком уже есть. Уточните его (модель, код ошибки) или найдите существующий вопрос.',
            'appliance_id.required' => 'Выберите тип прибора.',
        ];
    }
}
