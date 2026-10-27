<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginator::useBootstrapFour();
        Paginator::useBootstrapFive();

        // Время хранится в UTC; для показа людям: $date->inDisplayTimezone()
        \Carbon\Carbon::macro('inDisplayTimezone', function () {
            /** @var \Carbon\Carbon $this */
            return $this->copy()->timezone(config('app.display_timezone'));
        });
    }
}
