<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        // $users = User::withTrashed() // Если вы хотите включить удаленных пользователей
        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'мастер');
        })
            ->whereNull('deleted_at') // Получить только неудаленных пользователей
            ->get();

        return response()->json($users);
    }
}
