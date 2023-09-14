<?php

namespace App\Providers;

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

        View::composer('public.layouts.includes.sidebar', PublicComposer::class);
    }
}
