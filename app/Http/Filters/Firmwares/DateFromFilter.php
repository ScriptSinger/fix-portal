<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class DateFromFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('date_from')) {
            $builder->whereDate('date', '>=', request('date_from'));
        }

        return $next($builder);
    }
}
