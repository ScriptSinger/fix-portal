<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class SerialNumberFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('sn')) {
            $builder->where('data', 'like', '%' . request('sn') . '%');
        }

        return $next($builder);
    }
}
