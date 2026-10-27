<?php

namespace App\Http\Filters\Post;

use App\Support\SqlLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?title=… поиск по описанию статьи. */
class DescriptionFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->has('title')) {
            $builder->where('description', 'like', '%' . SqlLike::escape((string) $this->request->input('title')) . '%');
        }

        return $next($builder);
    }
}
