<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?extension=.eep */
class ExtensionFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('extension')) {
            $builder->where('extension', $this->request->input('extension'));
        }

        return $next($builder);
    }
}
