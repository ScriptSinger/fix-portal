<?php

namespace App\Http\Filters\Firmwares;


use Illuminate\Database\Eloquent\Builder;

class DataFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('data')) {
            $data = request('data');
            $builder->where('data', 'like', '%' . $data . '%');
        }

        return $next($builder);
    }
}
