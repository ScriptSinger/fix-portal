<?php

namespace App\Http\View\Composers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicComposer
{

    protected $posts;
    protected $categories;

    public function __construct(Post $posts, Category $categories)
    {

        $this->posts = $posts;
        $this->categories = $categories;
    }

    public function compose(View $view)
    {
        if (Cache::has('popular_categories')) {
            $popular_categories = Cache::get('popular_categories');
        } else {
            $popular_categories =  $this->categories->withCount('posts')->orderby('posts_count', 'desc')->get();
            Cache::put('popular_categories', $popular_categories, 30);
        }
        $view->with('popular_categories', $popular_categories);
        $view->with('popular_posts', $this->posts->orderby('views', 'desc')->limit(3)->get());
    }
}
