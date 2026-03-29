<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        $searchValue = request('search.value');
        if ($searchValue) {
            $builder->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', '%' . $searchValue . '%')
                    ->orWhere('platform', 'like', '%' . $searchValue . '%')
                    ->orWhere('extension', 'like', '%' . $searchValue . '%')
                    ->orWhere('data', 'like', '%' . $searchValue . '%')
                    ->orWhere('id', $searchValue);
            });
        }

        return $next($builder);
    }
}
