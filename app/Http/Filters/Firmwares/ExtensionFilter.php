<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class ExtensionFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('extension')) {
            $builder->where('extension', request('extension'));
        }

        return $next($builder);
    }
}
