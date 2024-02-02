<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FileUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::firstOrCreate(['email' => $data['email']], $data);
        return redirect()->route('users.index')->with('success', 'Пользователь успешно создан');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable',
            'name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|regex:/^\+?\d{1,4}?[-.\s]?\(?\d{1,6}?\)?[-.\s]?\d{1,9}[-.\s]?\d{1,9}$/',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = User::findOrFail($data['id']);
        $data = FileUploader::getInstance()
            ->setData($data)
            ->setModel($user)
            ->removePrev()
            ->resizeSave()
            ->getData();

        // $data['avatar'] = $user->processFileUpload($request, 'avatar', ['fileType' => 'png']);
        $user->update($data);

        return redirect()->back()->with('success', 'Пользователь успешно обновлен');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно удален');
    }
}
