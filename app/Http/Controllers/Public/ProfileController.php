<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FileUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();
        // Передаем данные пользователя в представление
        return view('public.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|regex:/^\+?\d{1,4}?[-.\s]?\(?\d{1,6}?\)?[-.\s]?\d{1,9}[-.\s]?\d{1,9}$/',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = $request->user();

        $data = FileUploader::getInstance()
            ->setData($data)
            ->setModel($user)
            ->removePrev()
            ->resizeSave()
            ->getData();

        // $data['avatar'] = $user->processFileUpload($request, 'avatar', ['fileType' => 'png']);
        $user->update($data);
        session()->flash('success', 'Профиль успешно обновлён!');
        return redirect('/profile?timestamp=' . now()->timestamp);
    }
}
