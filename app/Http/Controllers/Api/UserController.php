<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->get();
        return response()->json($users);
    }

    public function masters()
    {
        $users = User::withTrashed() // Если вы хотите включить удаленных пользователей
            ->whereHas('role', function ($query) {
                $query->where('name', 'мастер');
            })
            ->get();

        return response()->json($users);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return  response()->json(['message' => 'Пользователь успешно удален'], 200);;
    }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        if ($user) {
            $user->restore();
            return response()->json(['message' => 'Пользователь успешно восстановлен'], 200);
        } else {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }
    }
}
