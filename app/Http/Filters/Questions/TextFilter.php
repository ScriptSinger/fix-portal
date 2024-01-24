<?php

namespace App\Http\Filters\Questions;


use Illuminate\Database\Eloquent\Builder;

class TextFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        if (request()->has('text')) {

            $text = request('text');
            $builder->where('title', 'like', '%' . $text . '%');
        }

        return $next($builder);
    }
}
