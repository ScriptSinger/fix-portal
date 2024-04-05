<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class  FirmwareService
{


    public static function simple(Request $request, Builder $query, array $columns)
    {
        // Устанавливаем общее количество записей
        $recordsTotal = $query->count();

        // Применяем поиск (если есть)
        if ($request->has('search') && !empty($request->input('search.value'))) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $request->input('search.value') . '%');
            }
        }

        // Получаем общее количество записей после применения поиска
        $recordsFiltered = $query->count();

        // Применяем сортировку
        foreach ($request->input('order', []) as $order) {
            $query->orderBy($columns[$order['column']], $order['dir']);
        }

        // Применяем пагинацию
        $query->skip($request->input('start', 0))->take($request->input('length', 10));

        // Получаем данные
        $data = $query->get();

        // Возвращаем данные в формате, понятном DataTables
        return [
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}
