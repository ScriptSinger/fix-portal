<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?brand=Indesit */
class BrandFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('brand')) {
            $builder->where('brand', $this->request->input('brand'));
        }

        return $next($builder);
    }
}
