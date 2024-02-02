<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::all();
        return view('admin.questions.index', compact('questions'));
    }

    public function create()
    {
        $appliances = Appliance::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        return view('admin.questions.create', compact('appliances'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'appliance_id' => 'required|exists:appliances,id',
            'description' => 'required|string',
        ]);
        $user_id = auth()->user()->id;
        $data['user_id'] = $user_id;
        Question::create($data);
        return redirect()->route('admin.questions.index')->with('success', 'Вопрос добавлен');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        dd($id);
    }

    public function edit(string $id)
    {
        $appliances = Appliance::pluck('title', 'id'); // Получаем коллекцию объектов Eloquent.
        $question = Question::where('id', $id)->firstOrFail();
        return view('admin.questions.edit', compact('question', 'appliances'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'appliance_id' => 'required|exists:appliances,id',
            'description' => 'required|string',
        ]);
        $question = Question::where('id', $id)->firstOrFail();
        $question->update($data);
        return redirect()->route('admin.questions.index')->with('success', 'Вопрос обновлен');
    }

    public function destroy(string $id)
    {
        Question::find($id)->delete();
        return redirect()->route('admin.appliances.index')->with('success', 'Прибор успешно удален');
    }
}
