<?php

namespace App\Services;

use App\Models\Firmware;

class DuplicateService
{

    public function getDuplicates()
    {
        return Firmware::select('title', 'size', 'date', 'extension', 'platform', 'crc32', 'data')
            ->groupBy('title', 'size', 'date', 'extension', 'platform', 'crc32', 'data')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    public function countDuplicates()
    {
        return count($this->getDuplicates());
    }

    public function getDuplicatePairs()
    {
        $duplicates = $this->getDuplicates();

        // Переменная для хранения парами дублирующихся записей
        $duplicatePairs = [];

        foreach ($duplicates as $duplicate) {
            // Найти все записи, соответствующие текущей дублирующейся записи
            $pair = Firmware::where('title', $duplicate->title)
                ->where('size', $duplicate->size)
                ->where('date', $duplicate->date)
                ->where('extension', $duplicate->extension)
                ->where('platform', $duplicate->platform)
                ->where('crc32', $duplicate->crc32)
                ->where('data', $duplicate->data)
                ->get();


            // Добавить пару в результат, если найдены дублирующиеся записи
            if ($pair->count() > 1) {
                // Преобразовать коллекцию в массив для удобства
                $pairArray = $pair->toArray();

                // Добавить пару в массив
                $duplicatePairs[] = [
                    'first' => $pairArray[0], // первая запись
                    'second' => $pairArray[1] // вторая запись
                ];
            }
        }

        return $duplicatePairs;
    }
}
