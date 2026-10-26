<?php

namespace App\Http\View\Composers;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Customization;
use App\Models\Post;
use App\Models\Question;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicComposer
{

    protected $posts;
    protected $categories;
    protected $customization;

    public function __construct(Post $posts, Category $categories, Customization $customization)
    {

        $this->posts = $posts;
        $this->categories = $categories;
        $this->customization = $customization;
    }

    public function compose(View $view)
    {
        $cacheKey = 'public_composer_data';

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
        } else {
            $data = [
                'popular_categories' => $this->categories->withCount('posts')->orderBy('posts_count', 'desc')->limit(7)->get(),
                'popular_posts' => $this->posts->orderBy('views', 'desc')->limit(3)->get(),
                'latest_posts' => $this->posts->orderBy('created_at', 'desc')->limit(3)->get(),
                'popular_appliances' => Appliance::withCount('questions')->having('questions_count', '>', 0)->orderBy('questions_count', 'desc')->limit(10)->get(),
                'latest_questions' => Question::with('appliance')->orderBy('id', 'desc')->limit(5)->get(),
                'customization' => $this->customization->first(),
            ];

            Cache::put($cacheKey, $data, 30);
        }

        $view->with($data);
    }
}
