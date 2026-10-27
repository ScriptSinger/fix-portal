<?php

namespace App\Http\Filters\Firmwares;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?crc32=… (поле хранит контрольную сумму файла) */
class Crc32Filter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        if ($this->request->filled('crc32')) {
            $builder->where('crc32', $this->request->input('crc32'));
        }

        return $next($builder);
    }
}
