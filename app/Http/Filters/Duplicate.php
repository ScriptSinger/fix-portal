<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;

class Duplicate
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('is_duplicate')) {
            $builder->where('is_duplicate', request('is_duplicate') == true);
        }

        return $next($builder);
    }
}
