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
        // return redirect()->route('categories.index')->session()->flash('success', 'Категория добавлена');
        return redirect()->route('appliances.index')->with('success', 'Категория добавлена');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $appliance = Appliance::find($id);
        return view('admin.appliances.edit', compact('appliance'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'title' => 'required'
        ]);

        $appliance = Appliance::find($id);
        // $category->slug = null; // обновление slug
        $appliance->update($data);
        return redirect()->route('appliances.index')->with('success', 'Категория отредактирована');
    }

    public function destroy(string $id)
    {
        //
    }
}
