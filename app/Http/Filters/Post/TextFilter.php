<?php

namespace App\Http\Filters\Post;

use App\Support\SqlLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?text=… поиск по заголовку и описанию статьи. */
class TextFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        $term = $this->request->input('text');

        if (! empty($term)) {
            $like = '%' . SqlLike::escape((string) $term) . '%';
            $builder->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('description', 'like', $like));
        }

        return $next($builder);
    }
}
