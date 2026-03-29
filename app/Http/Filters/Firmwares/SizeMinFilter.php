<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class SizeMinFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('size_min')) {
            $builder->where('size', '>=', (int) request('size_min'));
        }

        return $next($builder);
    }
}
