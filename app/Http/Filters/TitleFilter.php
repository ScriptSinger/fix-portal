<?php

namespace App\Http\Filters;


use Illuminate\Database\Eloquent\Builder;

class TitleFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('title')) {
            $title = request('title');

            // Используем метод where для фильтрации по полю title
            $builder->where('title', 'like', '%' . $title . '%');
        }

        return $next($builder);
    }
}
