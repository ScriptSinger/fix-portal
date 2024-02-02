<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function index()
    {
        $appliances = Appliance::paginate(20);
        return view('admin.appliances.index', compact('appliances'));
    }

    public function create()
    {
        return view('admin.appliances.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required'
        ]);
        Appliance::create($data);
        return redirect()->route('admin.appliances.index')->with('success', 'Прибор добавлен');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $appliance = Appliance::findOrFail($id);
        return view('admin.appliances.edit', compact('appliance'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required'
        ]);
        $appliance = Appliance::find($id);
        $appliance->update($data);
        return redirect()->route('admin.appliances.index')->with('success', 'Отредактировано');
    }

    public function destroy(string $id)
    {
        $appliance = Appliance::find($id);
        if (!$appliance) {
            return redirect()->route('admin.appliances.index')->with('error', 'Прибор не найден');
        }
        if ($appliance->questions->count() > 0) {
            return redirect()->back()->with('error', 'Ошибка! Нельзя удалить прибор, у которого есть связанные записи.');
        }
        $appliance->delete();
        return redirect()->route('admin.appliances.index')->with('success', 'Прибор успешно удален');
    }
}
