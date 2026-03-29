<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class PlatformFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('platform')) {
            $builder->where('platform', request('platform'));
        }

        return $next($builder);
    }
}
