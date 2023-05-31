<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::paginate(2);
        return view('admin.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);
        Task::create($request->all());
        // return redirect()->route('tasks.index')->session()->flash('success', 'Задание добавлено');
        return redirect()->route('tasks.index')->with('success', 'Задание добавлено');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = Task::find($id);
        return view('admin.tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required'
        ]);
        $task = Task::find($id);
        $task->update($request->all());
        return redirect()->route('tasks.index')->with('success', 'Задание отредактировано');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $task = Task::find($id);
        // $task->delete();
        Task::destroy($id);
        return redirect()->route('tasks.index')->with('success', 'Задание удалено');
    }
}
