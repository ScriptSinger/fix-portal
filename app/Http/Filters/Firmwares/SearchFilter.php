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
                $q->where('title', 'like', '%' . $searchValue . '%');
                $this->applyStructuredDataSearch($q, $searchValue);

                if (ctype_digit((string) $searchValue)) {
                    $q->orWhere('id', (int) $searchValue);
                }
            });
        }

        return $next($builder);
    }

    private function applyStructuredDataSearch(Builder $query, string $search): void
    {
        $escaped = \App\Support\SqlLike::escape($search);
        $labels = ['Модель', 'Model', 'S/N', 'SN', 'Серийный номер', 'PNS', 'PNC', 'P/N', 'Part Number', 'Код прошивки', 'Firmware Code', 'Для S/N'];

        foreach ($labels as $label) {
            $query->orWhereRaw(
                "data LIKE ? ESCAPE '\\\\'",
                ['%<td>' . $label . '</td>%<td>' . $escaped . '%</td>%']
            );
        }
    }

}
