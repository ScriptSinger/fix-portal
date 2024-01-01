<?php

namespace App\Http\Sort\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class TitleSort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('title')) {
            $builder->orderBy('title', request()->title);
        }

        return $next($builder);
    }
}
