<?php

namespace App\Http\Sort\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class Crc32Sort
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('crc32')) {
            $builder->orderBy('crc32', request()->crc32);
        }
        return $next($builder);
    }
}
