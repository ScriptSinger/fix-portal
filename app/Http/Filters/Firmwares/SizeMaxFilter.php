<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class SizeMaxFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('size_max')) {
            $builder->where('size', '<=', (int) request('size_max'));
        }

        return $next($builder);
    }
}
