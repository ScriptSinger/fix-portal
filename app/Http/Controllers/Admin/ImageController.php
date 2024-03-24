<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index()
    {
        return view('admin.images.index');
    }

    public function show($id)
    {
        $images = Image::where('user_id', '=', $id)
            ->with('user')
            ->get();

        return view('admin.images.show', compact('images'));
    }

    public function grid()
    {
        $images = Image::all();
        return view('admin.images.grid', compact('images'));
    }
}
