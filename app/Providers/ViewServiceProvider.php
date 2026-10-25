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
        // Пилот: ссылка на pilot.* вариант маршрута, если он есть и мы внутри пилота; после переключения удаляется
        View::share('pr', fn (string $name, array $params = []) => route(
            request()->routeIs('pilot.*') && \Illuminate\Support\Facades\Route::has('pilot.' . $name) ? 'pilot.' . $name : $name,
            $params
        ));

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


            'public.layouts.includes.footer',

            // Пилот перехода на Vite + Tailwind
            'tw.*',
        ], PublicComposer::class);
    }
}
