<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customization;
use Illuminate\Http\Request;

class CustomizationController extends Controller
{

    public function edit()
    {
        $customization = Customization::firstOrNew();
        return view('admin.customs.create', compact('customization'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => 'max:255',
            'description' => 'max:255',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif'
        ]);


        $customization = Customization::firstOrNew();
        $id = $customization->id;

        if (!$customization->exists) {

            $customization->create($data);
        } else {

            $customization = Customization::where('id', $id);

            // $data['favicon'] = Customization::uploadFile($request, 'favicon',  $customization->favicon);

            // $data['logo'] = Customization::uploadFile($request, 'logo',  $customization->logo);
            // $data['banner'] = Customization::uploadFile($request, 'banner',  $customization->banner);

            $customization->update($data);
        }



        return redirect()->back()->with('success', 'Настройки обновлены успешно');
    }
}
