<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedUserAgent;
use Illuminate\Http\Request;

class BlockedUserAgentController extends Controller
{
    public function index()
    {
        return view('admin.blockeds.agents.index');
    }

    public function create()
    {
        return view('admin.blockeds.agents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_agent' => ['required'], // Поле обязательно для заполнения и должно быть действительным IP-адресом
        ]);
        BlockedUserAgent::create(['user_agent' => $data['user_agent']]);
        return redirect()->route('admin.agents.index')->with('success', 'User-agent добавлен в черный список');
    }

    public function edit(int $id)
    {
        $blocked = BlockedUserAgent::findOrFail($id);
        return view('admin.blockeds.agents.edit', compact('blocked'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'user_agent' => ['required'],
        ]);

        $blocked = BlockedUserAgent::findOrFail($id);
        $blocked->update($data);
        return redirect()->route('admin.agents.index')->with('success', 'User-agent изменен');
    }

    public function destroy(string $id)
    {
        $blocker = BlockedUserAgent::findOrFail($id);
        $blocker->forceDelete();
        return redirect()->route('admin.agents.index')->with('success', 'User-agent успешно удален из базы данных');
    }

    public function clear()
    {
        BlockedUserAgent::truncate();
        return redirect()->route('admin.agents.index')->with('success', ' Данные были очищены');
    }
}
