<?php

namespace App\Http\Sort\Firmwares;


use Illuminate\Database\Eloquent\Builder;

class ExtensionSort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('extension')) {
            $builder->orderBy('extension', request()->extension);
        }
        return $next($builder);
    }
}
