<?php

namespace App\Http\Sort\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class PlatformSort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('platform')) {
            $builder->orderBy('platform', request()->platform);
        }
        return $next($builder);
    }
}
