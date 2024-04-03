<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blocker;
use Illuminate\Http\Request;

class BlockedController extends Controller
{

    public function index()
    {
        return view('admin.blockeds.index');
    }

    public function create()
    {
        return view('admin.blockeds.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip', 'unique:blockers,ip_address'], // Поле обязательно для заполнения и должно быть действительным IP-адресом
        ]);
        Blocker::create(['ip_address' => $data['ip_address']]);
        return redirect()->route('admin.blockeds.index')->with('success', 'IP-адрес добавлен в черный список');
    }

    public function edit(int $id)
    {
        $blocked = Blocker::findOrFail($id);
        return view('admin.blockeds.edit', compact('blocked'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip', 'unique:blockers,ip_address'],
        ]);
        $blocked = Blocker::findOrFail($id);
        $blocked->update($data);
        return redirect()->route('admin.blockeds.index')->with('success', 'IP-адрес изменен');
    }


    public function destroy(string $id)
    {
        $blocker = Blocker::findOrFail($id);
        $blocker->forceDelete();
        return redirect()->route('admin.blockeds.index')->with('success', 'IP-адрес успешно удален из базы данных');
    }
}
