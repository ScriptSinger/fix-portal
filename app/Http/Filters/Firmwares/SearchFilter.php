<?php

namespace App\Http\Filters\Firmwares;

use App\Services\Firmware\DataSearch;
use App\Support\SqlLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Firmware;

/**
 * ?search=… Поиск идёт в два шага. Сначала по коротким полям (название, модель, серия, алиасы): это миллисекунды.
 * Разбор HTML-описания (поле data, 12 LIKE по каждой записи) занимает около секунды, поэтому
 * подключаем его только если короткие поля ничего не нашли. Наличие совпадений кэшируем,
 * чтобы повторяющиеся запросы (в основном от краулеров) не ходили в базу заново.
 */
class SearchFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('search')) {
            $this->apply($builder, trim((string) $this->request->input('search')));
        }

        return $next($builder);
    }

    private function apply(Builder $query, string $search): void
    {
        $fast = function ($q) use ($search) {
            // «wisl 105», «WISL-105» и «WISL105» должны находить одну и ту же модель
            $compact = mb_strtoupper(preg_replace('/[\s\-_]+/u', '', $search) ?? $search);

            $q->where('title', 'like', '%' . SqlLike::escape($search) . '%')
                ->orWhere('model', 'like', '%' . SqlLike::escape(mb_strtoupper($search)) . '%')
                ->orWhere('model', 'like', '%' . SqlLike::escape($compact) . '%')
                ->orWhere('series', $compact)
                ->orWhere('aliases', 'like', '%' . SqlLike::escape($search) . '%');

            if (ctype_digit($search)) {
                $q->orWhere('id', (int) $search);
            }
        };

        $found = Cache::remember('firmware.search.' . md5(mb_strtolower($search)), 600, fn () => Firmware::query()->where($fast)->exists());

        if ($found) {
            $query->where($fast);

            return;
        }

        $query->where(function ($q) use ($fast, $search) {
            $q->where($fast);
            DataSearch::orWhereMatches($q, $search);
        });
    }
}
