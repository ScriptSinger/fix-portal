<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class DateToFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('date_to')) {
            $builder->whereDate('date', '<=', request('date_to'));
        }

        return $next($builder);
    }
}
