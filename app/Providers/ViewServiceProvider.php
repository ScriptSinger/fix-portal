<?php

namespace App\Providers;

use App\Http\View\Composers\FooterComposer;
use App\Http\View\Composers\HeaderComposer;
use App\Http\View\Composers\PublicComposer;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer([
            'public.layouts.includes.header',
            'public.posts.index',
            'public.questions.index',
            'public.questions.search',
            'public.posts.search',


            'public.layouts.widgets.sidebar.prime_categories',
            'public.layouts.widgets.sidebar.prime_posts',

            'public.layouts.widgets.footer.popular_posts',
            'public.layouts.widgets.footer.popular_categories',


            'public.layouts.includes.footer'
        ], PublicComposer::class);
    }
}
