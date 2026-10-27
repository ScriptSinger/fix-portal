<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?series=wisl (серии хранятся в верхнем регистре) */
class SeriesFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('series')) {
            $builder->where('series', mb_strtoupper(trim((string) $this->request->input('series'))));
        }

        return $next($builder);
    }
}
