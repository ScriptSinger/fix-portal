<?php

namespace App\Http\Filters\Questions;

use App\Support\SqlLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?text=… поиск по заголовку вопроса. */
class TextFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('text')) {
            $builder->where('title', 'like', '%' . SqlLike::escape((string) $this->request->input('text')) . '%');
        }

        return $next($builder);
    }
}
