<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DeletedUserController extends Controller
{
    public function index()
    {
        $deletedUsers = User::onlyTrashed()->paginate(20);
        return view('admin.users.deleted', compact('deletedUsers'));
    }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        $user->restore();

        return redirect()->route('deleted-users.index')->with('success', 'Пользователь восстановлен успешно');
    }
}
