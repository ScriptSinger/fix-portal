<?php

namespace App\Http\Filters\Post;


use Illuminate\Database\Eloquent\Builder;

class DescriptionFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('title')) {
            $description = request('title');
            $builder->where('description', 'like', '%' . $description . '%');
        }

        return $next($builder);
    }
}
