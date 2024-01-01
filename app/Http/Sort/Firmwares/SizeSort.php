<?php

namespace App\Http\Sort\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class SizeSort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('size')) {
            $builder->orderBy('size', request()->size);
        }
        return $next($builder);
    }
}
