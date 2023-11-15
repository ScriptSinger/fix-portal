<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Customization;
use App\Models\Post;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    // Не используется
    public function index()
    {

        $customization = Customization::first();

        $posts = Post::with('category')->orderBy('id', 'desc')->paginate(2);
        return view('public.welcome', compact('posts'));
    }
}
