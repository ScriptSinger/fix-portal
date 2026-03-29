<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class Crc32Filter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->filled('crc32')) {
            $builder->where('crc32', request('crc32'));
        }

        return $next($builder);
    }
}
