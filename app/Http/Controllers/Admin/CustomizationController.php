<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customization;
use App\Services\FileUploader;
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
        $data = FileUploader::getInstance()
            ->setData($data)
            ->setModel($customization)
            ->setSupDir('customization')
            ->removePrev()
            ->save()
            ->getData();

        $customization->updateOrInsert(['id' => 1], $data);
        return redirect()->back()->with('success', 'Настройки обновлены успешно');
    }

    public function destroy()
    {
        Customization::truncate();
        return redirect()->back()->with('success', 'Удалено');
    }
}
