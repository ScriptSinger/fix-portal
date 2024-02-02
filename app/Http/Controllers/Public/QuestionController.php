<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Questions\TextFilter;

use App\Models\Appliance;
use App\Models\Question;
use App\Services\FileUploader;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

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

            ->orderBy('id', 'desc')
            ->paginate(50);


        return view('public.questions.index', compact('questions'));
    }

    public function create()
    {
        $appliances = Appliance::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        return view('public.questions.create', compact('appliances'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'appliance_id' => 'required|exists:appliances,id',
            'description' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Проверка на изображения и ограничение размера до 2 МБ
            'photos' => 'nullable|array|max:4', // Максимальное количество файлов в массиве photos: 4

        ]);
        // $user_id = $request->user()->id; 
        $user_id = auth()->user()->id;
        $path = date('Y-m-d') . "/" . $user_id;

        $data = FileUploader::getInstance()
            ->setData($data)
            ->setSupDir('question')
            ->setSubDir($path)
            ->multipleSave()
            ->getData();

        $data['user_id'] = $user_id;
        Question::create($data);
        return redirect()->route('questions.index');
    }

    public function show(string $slug)
    {
        $question = Question::where('slug', $slug)->firstOrFail();
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

        $data = $request->validate([
            'title' => 'required|string',
            'appliance_id' => 'required|exists:appliances,id',
            'description' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Проверка на изображения и ограничение размера до 2 МБ
            'photos' => 'nullable|array|max:4', // Максимальное количество файлов в массиве photos: 4
        ]);

        $question = Question::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $question);

        $user_id = $request->user()->id;
        $path = date('Y-m-d') . "/" . $user_id;
        $data = FileUploader::getInstance()
            ->setData($data)
            ->setModel($question)
            ->setSupDir('question')
            ->setSubDir($path)
            ->multipleRemovePrev()
            ->multipleSave($path)
            ->getData();

        $data['user_id'] = $user_id;
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
}
