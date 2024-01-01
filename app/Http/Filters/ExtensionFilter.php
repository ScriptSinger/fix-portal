<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;

class ExtensionFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('extension')) {

            $extension = request('extension');
            $builder->where('extension', 'like', '%' . $extension . '%');
        }

        return $next($builder);
    }
}
