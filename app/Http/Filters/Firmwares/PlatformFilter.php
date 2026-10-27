<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?platform=Arcadia. platform_norm склеивает Arcadia / ARCADIA / arcadia; старое поле оставляем для записей без обогащения. */
class PlatformFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('platform')) {
            $platform = $this->request->input('platform');
            $builder->where(fn ($q) => $q->where('platform_norm', $platform)->orWhere('platform', $platform));
        }

        return $next($builder);
    }
}
