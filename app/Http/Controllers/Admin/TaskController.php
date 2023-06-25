<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::paginate(2);
        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('admin.tasks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);
        Task::create($request->all());
        // return redirect()->route('tasks.index')->session()->flash('success', 'Задание добавлено');
        return redirect()->route('tasks.index')->with('success', 'Задание добавлено');
    }

    public function edit(string $id)
    {
        $task = Task::find($id);
        return view('admin.tasks.edit', compact('task'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required'
        ]);
        $task = Task::find($id);
        $task->update($request->all());
        return redirect()->route('tasks.index')->with('success', 'Задание отредактировано');
    }

    public function destroy(string $id)
    {
        // $task = Task::find($id);
        // $task->delete();
        Task::destroy($id);
        return redirect()->route('tasks.index')->with('success', 'Задание удалено');
    }
}
