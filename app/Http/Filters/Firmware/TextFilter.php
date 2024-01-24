<?php

namespace App\Http\Filters\Firmware;


use Illuminate\Database\Eloquent\Builder;

class TextFilter
{
    public function handle(Builder $builder, \Closure $next)
    {
        $text = request('text');

        if ($text) {
            // Используем метод where для фильтрации по полям title и data
            $builder->where(function ($query) use ($text) {
                $query->where('title', 'like', '%' . $text . '%')
                    // ->orWhere('size', 'like', '%' . $text . '%')
                    ->orWhere('date', 'like', '%' . $text . '%')
                    ->orWhere('extension', 'like', '%' . $text . '%')
                    ->orWhere('platform', 'like', '%' . $text . '%')
                    ->orWhere('data', 'like', '%' . $text . '%');
            });
        }

        return $next($builder);
    }
}
