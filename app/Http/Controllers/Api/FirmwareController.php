<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Filters\Firmwares\Crc32Filter;
use App\Http\Filters\Firmwares\DataFilter;
use App\Http\Filters\Firmwares\DateFromFilter;
use App\Http\Filters\Firmwares\DateToFilter;
use App\Http\Filters\Firmwares\ExtensionFilter;
use App\Http\Filters\Firmwares\ModelFilter;
use App\Http\Filters\Firmwares\PlatformFilter;
use App\Http\Filters\Firmwares\SearchFilter;
use App\Http\Filters\Firmwares\SerialNumberFilter;
use App\Http\Filters\Firmwares\SizeMaxFilter;
use App\Http\Filters\Firmwares\SizeMinFilter;
use App\Models\Firmware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pipeline\Pipeline;

class FirmwareController extends Controller
{

    public function index(Request $request)
    {
        $draw = (int) $request->input('draw', 0);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $baseQuery = Firmware::query();
        $totalRecords = $baseQuery->count();

        $query = app(Pipeline::class)
            ->send(Firmware::query())
            ->through([
                PlatformFilter::class,
                ExtensionFilter::class,
                DateFromFilter::class,
                DateToFilter::class,
                SizeMinFilter::class,
                SizeMaxFilter::class,
                Crc32Filter::class,
                ModelFilter::class,
                SerialNumberFilter::class,
                SearchFilter::class,
                DataFilter::class,
            ])
            ->thenReturn();

        $recordsFiltered = $query->count();

        $columnMap = [
            0 => 'id',
            1 => 'title',
            2 => 'size',
            3 => 'date',
            4 => 'extension',
            5 => 'platform',
        ];

        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columnMap[$orderColumnIndex])) {
            $query->orderBy($columnMap[$orderColumnIndex], $orderDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $firmwares = $query->get();

        $data = $firmwares->map(function (Firmware $firmware) {
            $firmwarePath = 'firmwares/' . $firmware->title . ($firmware->extension ?? '');
            return [
                'id' => $firmware->id,
                'title' => $firmware->title,
                'model_name' => $firmware->model_name,
                'serial_number' => $firmware->serial_number,
                'size' => $firmware->size,
                'date' => $firmware->date,
                'extension' => $firmware->extension,
                'platform' => $firmware->platform,
                'crc32' => $firmware->crc32,
                'data' => $firmware->data,
                'has_file' => Storage::disk('public')->exists($firmwarePath),
                'deleted_at' => $firmware->deleted_at,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
