<?php

namespace App\Http\Sort\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class IdSort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('id')) {
            $builder->orderBy('id', request()->id);
        }
        return $next($builder);
    }
}
