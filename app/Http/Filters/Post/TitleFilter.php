<?php

namespace App\Http\Filters\Post;


use Illuminate\Database\Eloquent\Builder;

class TitleFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('title')) {
            $title = request('title');
            $builder->where('title', 'like', '%' . $title . '%');
        }

        return $next($builder);
    }
}
