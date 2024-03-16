<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index()
    {
        return view('admin.files.index');
    }

    public function show($id)
    {
        $files = File::where('user_id', '=', $id)
            ->with('user')
            ->get();

        return view('admin.files.show', compact('files'));
    }

    public function grid()
    {
        $files = File::all();
        return view('admin.files.grid', compact('files'));
    }
}
