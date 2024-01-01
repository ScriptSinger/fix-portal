<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;

class PlatformFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('platform')) {

            $platform = request('platform');

            $builder->where('platform', 'like', '%' . $platform . '%');
        }

        return $next($builder);
    }
}
