<?php

namespace App\Http\Filters\Post;

use Illuminate\Database\Eloquent\Builder;

class TextFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        $searchTerm = request('text');

        if (!empty($searchTerm)) {
            $builder->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        return $next($builder);
    }
}
