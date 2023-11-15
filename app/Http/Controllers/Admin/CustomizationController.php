<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customization;
use Illuminate\Http\Request;

class CustomizationController extends Controller
{

    public function edit()
    {
        $customization = Customization::first();
        return view('admin.customs.create_edit', compact('customization'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => 'max:255',
            'description' => 'max:300',
            'copyright' => 'max:255',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif'
        ]);



        $customization = Customization::firstOrNew(['id' => 1], $data); // Здесь мы создаем объект
        $attributes = ['favicon', 'logo', 'banner'];

        foreach ($attributes as $attribute) {
            $data[$attribute] = $customization->uploadFile($request, $attribute, $customization->{$attribute}, false);
        }

        $customization->updateOrInsert(['id' => 1], $data);

        return redirect()->back()->with('success', 'Настройки обновлены успешно');
    }
}
