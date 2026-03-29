<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class ModelFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('model')) {
            $builder->where('data', 'like', '%' . request('model') . '%');
        }

        return $next($builder);
    }
}
